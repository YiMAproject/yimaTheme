<?php
namespace yimaTheme\Manager;

use yimaTheme\Manager;
use yimaTheme\Theme\Locator;
use yimaTheme\Theme\LocatorDefaultInterface;
use yimaTheme\Theme\LocatorInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver as ViewResolver;

/**
 * Class DefaultListenerAggregate
 *
 * @package yimaTheme\Manager
 */
class DefaultListenerAggregate extends Manager implements
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
     * @var array Initialized Themes
     */
    protected $attainedThemes = array();

    /**
     * @var Manager
     */
    protected $manager;

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
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, array($this, 'onMvcBootstrap'), 100000);

        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH, array($this, 'onDispatchThemeBootstrap'), -95);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this,'onDispatchThemeBootstrap'), -95);

        $events->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH,array($this, 'onDispatchSpecLayout'), -99);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR, array($this,'onDispatchSpecLayout'), -99);
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
        $r = $e->getResult();
        if (!$r instanceof ViewModel)
            return; // we don't get Renderer Result

        // allow viewScripts to append with others on content variable
        else $r->setAppend(true);

        $this->checkMVC(); // test application startup config to match our need

        /** @var $themeLocator Locator */
        $themeLocator = clone $this->getThemeLocator(); // we have to detach strategies
        
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
                $childTheme = $theme;

                // attain to next template
                $lastStrategy = $themeLocator->getResolverObject()
                    ->getLastStrategyFound();
                $themeLocator->getResolverObject()
                    ->dettach($lastStrategy); // remove last detector

                $theme = $themeLocator->getPreparedThemeObject();
                if ($theme)
                    $theme->addChild($childTheme, null, true);
                else
                    $theme = $childTheme->setFinalTheme(); // we have not other theme after this child
            }
        }

        // set themeObject as ViewModel
        $defTemplate = $e->getViewModel()
            ->getTemplate();
        if (!$theme->getTemplate())
            $theme->setTemplate($defTemplate); // set default template name
        $e->setViewModel($theme);

        // add path stacks
        $pathStacks = array_reverse($pathStacks); // child top and final theme must list last
        $this->addThemePathstack($pathStacks);
    }

    protected function setThemeManager(Manager $themeManager)
    {
        $this->manager = $themeManager;
    }

    /**
     * Add Requested template path to Stack of ViewTemplatePathStack
     *
     * @param array $paths
     * @internal param \Zend\Mvc\MvcEvent $e
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
        $r = $e->getResult();
        if (! $r instanceof ViewModel )
            return;

        $model = $e->getViewModel();

        $themeLocator  = $this->getThemeLocator();

        // we want theme path stack registered before
        #$this->onDispatchThemeBootstrap($e);

        // get Layout from Locator
        $mvcLayout = $themeLocator->getMvcLayout($e);
        if ($mvcLayout) {
            // we want same on theme layout name if not set
            $model->setTemplate($mvcLayout);
        }
    }

    /**
     * Check Current MVC View Resolver To Match With Class Strategy
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
     * @param LocatorDefaultInterface $themeLocator
     *
     * @return $this
     */
    public function setThemeLocator(LocatorDefaultInterface $themeLocator)
    {
        $this->sm->setInvokableClass('yimaTheme\ThemeLocator', $themeLocator);

        $this->themeLocator = $themeLocator;

        return $this;
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
