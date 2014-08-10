<?php
namespace yimaTheme\Manager;

use yimaTheme\Theme\Locator;
use yimaTheme\Theme\LocatorDefaultInterface;
use yimaTheme\Theme\LocatorInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver as ViewResolver;

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
     * @var Locator
     */
    protected $themeLocator;

    /**
     * @var array Bootstraped Themes
     */
    protected $attainedThemes = array();

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
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, array($this,'onMvcBootstrap'), 100000);

        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH, array($this,'onDispatchThemeBootstrap'), -95);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this,'onDispatchThemeBootstrap'), -95);

        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH,array($this,'onDispatchSpecLayout'), -99);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR,array($this,'onDispatchSpecLayout'), -99);

//        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_RENDER, array($this,'widgetizeIt'),-1000);
    }

    // --- Events Methods ---------------------------------------------------------------------------------------------------------------------

    /**
     * MVC Event Listener
     *
     * @param MvcEvent $e
     */
    public function onMvcBootstrap(MvcEvent $e) { }
        
    /**
     * MVC Event Listener
     * : bootstrap themes
     *
     * @param MvcEvent $e
     */
    public function onDispatchThemeBootstrap(MvcEvent $e) 
    {
        $this->checkMVC(); // test application startup config to match our need

        /** @var $themeLocator Locator */
        $themeLocator = clone $this->getThemeLocator();
        
        $pathStacks = array(); 
        
        $theme = $themeLocator->getPreparedThemeObject();
        while($theme) {
            // store attained themes list
            $this->attainedThemes[] = $theme;
            $pathStacks[] = $theme->getThemesPath().DIRECTORY_SEPARATOR. $theme->getName();
            
            // initialize theme bootstrap
            if (!$theme->isInitialized())
                $theme->init();
            
            if ($theme->isFinalTheme())
                break;
            else {
                // attain to next template
                $lastStrategy = $themeLocator->getResolverObject()
                    ->getLastStrategyFound();
                $themeLocator->getResolverObject()
                    ->dettach($lastStrategy); // remove last detector

                $theme = $themeLocator->getPreparedThemeObject();
            }
        }
        
        // add path stacks
        $pathStacks = array_reverse($pathStacks); // child top and finaltheme must list last
        $this->addThemePathstack($pathStacks);
    }

    /**
     * Add Requested template path to Stack of ViewTemplatePathStack
     *
     * @param MvcEvent $e
     * @throws \Exception
     */
    protected function addThemePathstack(array $paths)
    {
        $viewTemplatePathStack = $this->sm->get('ViewTemplatePathStack');
        foreach ($paths as $path) {
            $viewTemplatePathStack->addPath($path);
        }
    }

    /**
     * Change layout
     *
     * @param MvcEvent $e
     */
    public function onDispatchSpecLayout(MvcEvent $e)
    {
        $model = $e->getResult();
        if (! $model instanceof ViewModel ) {
            return;
        }

        $themeLocator  = $this->getThemeLocator();
        $preparedTheme = $themeLocator->getPreparedThemeObject();
        if (!$preparedTheme) {
            // we are not attained theme name
            return;
        }
        
        // we want theme pathstack registered before
        $this->onDispatchThemeBootstrap($e);     

        // get Layout from Locator
        $mvcLayout = $themeLocator->getMvcLayout($e);
        if ($mvcLayout) {
            // we want same on theme layout name if not set
            $preparedTheme->setLayout($mvcLayout);
        }

        $layout = $preparedTheme->getLayout();
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
        $themeObject  = $themeLocator->getPreparedThemeObject();

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

    // -------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get ThemeLocator
     *
     * @return LocatorDefaultInterface
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
    public function setThemeLocator(LocatorDefaultInterface $themeLocator)
    {
        $this->themeLocator = $themeLocator;
    }

    /**
     * Get Default Theme Locator for registered services in serviceManager
     *
     * @return Locator
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

        return $defaultThemeLocator;
    }

    // --- implemented methods ------------------------------------------------------------------------------------------------------------------

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
