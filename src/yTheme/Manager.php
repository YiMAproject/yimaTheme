<?php
namespace yTheme;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\SharedEventAggregateAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Resolver as ViewResolver;

use Zend\EventManager\SharedEventManager;

use yTheme\Theme\LocatorInterface;

use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ModelInterface as ViewModel;

class Manager implements ManagerInterface
{
    protected $isInitialized = false;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /*
     * @var SharedEventManager
     */
    protected $events;

    /**
     * @var LocatorInterface
     */
    protected $themeLocator;

    public function __construct(SharedEventManager $events, LocatorInterface $themeLocator = null)
    {
        if (isset($themeLocator)) {
            $this->setThemeLocator($themeLocator);
        }

        $this->events = $events;
        $this->attachDefaultListeners();
    }

    public function setThemeLocator(LocatorInterface $themeLocator)
    {
        $this->themeLocator = $themeLocator;
    }

    public function getThemeLocator()
    {
        if (! $this->themeLocator) {
            // try to get theme locator from serviceManager
            $this->themeLocator = $this->getServiceManager()->get('yTheme\ThemeLocator');
        }

        return $this->themeLocator;
    }

    /**
     * Register the default event listeners
     *
     */
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();

        // themes are loaded before bootstrap viewManager to load theme config first
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, array($this,'initialize'),100000);

        // we need pathstack initialized before injecting spec layouts
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this,'addThemePathstack'),-95);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH, array($this,'addThemePathstack'),-95);

        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR,array($this,'injectSpecLayout'),-99);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH,array($this,'injectSpecLayout'),-99);

        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_RENDER, array($this,'widgetIt'),-1000);
    }

    /**
     * Theme raa tashkhis midahad
     * config haaye aan raa load va raah andaazi mikonad
     *
     * @param MvcEvent $e
     * @return bool
     */
    public function initialize(MvcEvent $e)
    {
        if ($this->isInitialized()) {
            return false;
        }

        $serviceManager = $e->getApplication()->getServiceManager();
        $this->setServiceManager($serviceManager);

        $themeLocator = $this->getThemeLocator();
        if (method_exists($themeLocator,'initialize')) {
            $themeLocator->initialize();
        }

        $this->isInitialized = true;

        return $this;
    }

    // Event Methods ..................................................................................

    /**
     * Add Requested template path to Stack of ViewTemplatePathStack
     *
     * @param MvcEvent $e
     * @throws \Exception
     */
    public function addThemePathstack(MvcEvent $e)
    {
        if (! $this->isInitialized() ) {
            return;
        }

        $this->checkMVC(); // test application startup config to match our need

        $path = $this->getThemeLocator()->getPathName();

        $viewTemplatePathStack = $this->getServiceManager()->get('ViewTemplatePathStack');
        $viewTemplatePathStack->addPath($path);
    }

    public function injectSpecLayout(MvcEvent $e)
    {
        if (! $this->isInitialized() ) {
            return;
        }

        $model = $e->getResult();
        if (! $model instanceof ViewModel ) {
            return;
        }

        // we want theme pathstack registered before
        $this->addThemePathstack($e);

        // get Layout from Locator
        $this->getThemeLocator()->getOptions()->setParam('MvcEvent', $e);
        $layout = $this->getThemeLocator()->getLayout();

        if ($layout) {
            $model = $e->getViewModel();
            $model->setTemplate($layout);
        }
    }

    public function widgetIt(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        $viewModel = $e->getViewModel();
        if (! $viewModel instanceof ViewModel) {
            return;
        }

        // load widgets into
        $sm = $e->getApplication()->getServiceManager();

        $themeProps = $sm->get('yTheme\ThemeLocator')->getOptions()->getProps();
        $layout     = $viewModel->getTemplate();

        $areas = isset($themeProps['widgets'][$layout]) ?$themeProps['widgets'][$layout] :array();
        foreach($areas as $area => $widgets)
        {
            if (! is_array($widgets) ) {
                // convert it to array for itterate over
                $widgets = array($widgets);
            }

            foreach ($widgets as $w) {
                if (is_string($w) && $sm->has($w)) {
                    $w = $sm->get($w);
                    if (is_object($w) && method_exists($w,'__toString')) {
                        $w = (string) $w;
                    }
                }

                if ($w instanceof ViewModel) {
                    $viewModel->addChild($w, $area, true);
                } else if (is_string($w)) {
                    $viewModel->{$area} .= $w;
                }
            }
        }
    }

    // ....................................................................................................

    /**
     * Check mikonad ke aayaa in theme manager mitavaanad ba tavajoh
     * be saakhtar e load shodan e konooni e system kaar konad?
     *
     * @return bool
     * @throws \Exception
     */
    protected function checkMVC()
    {
        // check ViewResolver service to match our need. {
        $viewResolver   = $this->getServiceManager()->get('ViewResolver');

        $return = true;
        if ($viewResolver instanceof ViewResolver\AggregateResolver) {
            if ($viewResolver->count() == 2) {
                $defResolvers = array('Zend\View\Resolver\TemplateMapResolver','Zend\View\Resolver\TemplatePathStack');
                foreach($viewResolver->getIterator()->toArray() as $i=>$ro) {
                    if ($defResolvers[$i] != get_class($ro)) {
                        $return = false;
                        break;
                    }
                }
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }

        if (! $return) {
            // $message = 'default resolver change and codes may not work correctly';

            // ...
            // log or attention to developer.

        }
        // ... }

        $viewTemplatePathStack   = $this->getServiceManager()->get('ViewTemplatePathStack');
        if (! $viewTemplatePathStack instanceof ViewResolver\TemplatePathStack) {
            throw new \Exception('yTheme work with PathStack');
        }

        return $return;
    }

    /**
     * Determine theme is loaded or not?
     *
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * Retrieve the event manager
     *
     * @return SharedEventAggregateAwareInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        if (! $this->serviceManager) {
            throw new \Exception('ServiceManager not set yet!');
        }

        return $this->serviceManager;
    }

}