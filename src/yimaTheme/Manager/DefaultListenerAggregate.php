<?php
namespace yimaTheme\Manager;

use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Class DefaultListenerAggregate
 *
 * @package yimaTheme\Manager
 */
class DefaultListenerAggregate implements
    SharedListenerAggregateInterface,
    ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the SharedEventManager
     * implementation will pass this to the aggregate.
     *
     * @param SharedEventManagerInterface $events
     */
    public function attachShared(SharedEventManagerInterface $events)
    {
        // we need pathstack initialized before injecting spec layouts
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, array($this,'onMvcBootstrap'), 100000);

//        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH, array($this,'addThemePathstack'),-95);
//        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this,'addThemePathstack'),-95);
//
//        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH,array($this,'injectSpecLayout'),-99);
//        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR,array($this,'injectSpecLayout'),-99);
//
//        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_RENDER, array($this,'widgetizeIt'),-1000);
    }

    public function onMvcBootstrap(MvcEvent $e)
    {
        /*// initialize themeLocator
        if (method_exists($this->getThemeLocator(), 'init')) {
            $this->getThemeLocator()->init();
        }*/
    }

    /**
     * Add Requested template path to Stack of ViewTemplatePathStack
     *
     * @param MvcEvent $e
     * @throws \Exception
     */
    public function addThemePathstack(MvcEvent $e = null)
    {
        $this->checkMVC(); // test application startup config to match our need

        $theme = $this->getThemeLocator()->getTheme();

        $path = $theme->getThemesPath();
        $path = $path .DS. $theme->getName();

        $sl = ($e) ? $e->getApplication()->getServiceManager() : $this->sm;
        $viewTemplatePathStack = $sl->get('ViewTemplatePathStack');
        $viewTemplatePathStack->addPath($path);
    }

    /**
     * Change layout
     *
     * @param MvcEvent $e
     */
    public function injectSpecLayout(MvcEvent $e)
    {
        $model = $e->getResult();
        if (! $model instanceof ViewModel ) {
            return;
        }

        // we want theme pathstack registered before
        $this->addThemePathstack($e);

        // get Layout from Locator
        $layout = $this->getThemeLocator()->getMvcLayout($e);
        if ($layout != $this->getThemeLocator()->getTheme()->getLayout()) {
            // we wan't same on theme layout name if not set
            $this->getThemeLocator()->getTheme()->setLayout($layout);
        }

        if ($layout) {
            $model = $e->getViewModel();
            $model->setTemplate($layout);
        }
        // else { let other events do somethings .... }
    }

    public function widgetizeIt(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        $viewModel = $e->getViewModel();
        if (! $viewModel instanceof ViewModel) {
            return;
        }

        // load widgets into {
        $themeLocator = $this->getThemeLocator();
        $themeObject  = $themeLocator->getTheme();

        $sm = $this->sm;

        $config       = $themeLocator->getConfig();
        $config       = $config['themes'];
        $themeName = $themeObject->getName();
        $config       = (isset($config[$themeName])) ? $config[$themeName] : array();
        $layout       = $viewModel->getTemplate();
        $areas = isset($config['widgets'][$layout]) ? $config['widgets'][$layout] : array();
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
        $viewResolver   = $this->sm->get('ViewResolver');

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

        $viewTemplatePathStack   = $this->sm->get('ViewTemplatePathStack');
        if (! $viewTemplatePathStack instanceof ViewResolver\TemplatePathStack) {
            throw new \Exception('yimaTheme work with PathStack');
        }

        return $return;
    }

    /**
     * Get ThemeLocator
     *
     * @return LocatorInterface
     */
    public function getThemeLocator()
    {
        if (! $this->themeLocator) {
            // use default theme locator to resolve theme object
            $this->themeLocator = $this->getDefaultThemeLocator();
        }

        return $this->themeLocator;
    }

    /**
     * Set ThemeLocator
     *
     * @param LocatorInterface $themeLocator
     */
    public function setThemeLocator(LocatorInterface $themeLocator)
    {
        $this->themeLocator = $themeLocator;
    }

    /**
     * Get Default Theme Locator for registered services in serviceManager
     *
     * @return Theme\Locator
     * @throws \Exception
     */
    protected function getDefaultThemeLocator()
    {
        /** @var $defaultThemeLocator \yimaTheme\Theme\Locator */
        $defaultThemeLocator = $this->sm->get('yimaTheme\ThemeLocator');
        if (!$defaultThemeLocator instanceof LocatorDefaultInterface) {
            throw new \Exception(
                'Default Theme Locator Service (yimaTheme\ThemeLocator) must instance of yimaTheme\Theme\LocatorDefaultInterface'
            );
        }

        $defaultThemeLocator->setManager($this);

        return $defaultThemeLocator;
    }

    /**
     * Detach all previously attached listeners
     *
     * @param SharedEventManagerInterface $events
     */
    public function detachShared(SharedEventManagerInterface $events)
    {
        // TODO: Implement detachShared() method.
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->sm = $serviceManager;
    }
}
