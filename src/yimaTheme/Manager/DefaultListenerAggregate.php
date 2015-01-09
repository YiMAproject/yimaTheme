<?php
namespace yimaTheme\Manager;

use yimaTheme\Manager;
use yimaTheme\Theme\Locator;
use yimaTheme\Theme\LocatorDefaultInterface;
use yimaTheme\Theme\LocatorInterface;
use yimaTheme\Theme\Theme;
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
     * @var array Attained Themes PathStack
     */
    protected $pathStacks = array();

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
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, array($this, 'onMvcBootstrapLast'), -100000);

        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_RENDER, array($this, 'onRenderAddPathStacks'), -900);
        $events->attach('Zend\Mvc\Application', MvcEvent::EVENT_RENDER, array($this, 'onRenderSpecLayout'), -1000);
    }

    // --- Events Methods ---------------------------------------------------------------------------------------------------------------------

    /**
     * MVC Event Listener
     *
     * - get Theme From Locator
     * - initialize theme if not
     * - set theme template to default viewModel template name
     *   if not exists
     * - set Resolved Theme to Event as ViewModel
     *
     * @param MvcEvent $e
     */
    public function onMvcBootstrapLast(MvcEvent $e)
    {
        $this->checkMVC(); // test application startup config to match our need

        /** @var $themeLocator Locator */
        $themeLocator = clone $this->getThemeLocator(); // we have to detach strategies
        // Attain to Base ViewModel to Children Themes Append To ... {
        $themAsViewModel = false;
        while($theme = $themeLocator->getPreparedThemeObject())
        {
            // store attained themes list
            $this->attainedThemes[] = $theme;

            $this->pathStacks[] = $theme->getThemesPath().DIRECTORY_SEPARATOR. $theme->getName();

            // initialize theme bootstrap, also we can know theme final after initialize
            if (!$theme->isInitialized())
                $theme->init();

            if ($theme->isFinalTheme()) {
                $themAsViewModel = spl_object_hash($theme); // use to add children themes to final
                $defTemplate = $e->getViewModel()->getTemplate();
                if (!$theme->getTemplate())
                    $theme->setTemplate($defTemplate); // set default template name

                // set themeObject as ViewModel
                $e->setViewModel($theme);
                break;
            }

            // attain to next template
            $lastStrategy = $themeLocator->getResolverObject()
                ->getLastStrategyFound();
            $themeLocator->getResolverObject()
                ->dettach($lastStrategy); // remove last detector
        }

        /** @var Theme $t */
        foreach($this->attainedThemes as $t) {
            if ($themAsViewModel && spl_object_hash($t) === $themAsViewModel)
                continue; // This is a Final Theme Child will added to

            if ($t->getTemplate())
                // if child theme has a template to render
                $e->getViewModel()->addChild($t, null, true);
        }
        // ... }
    }
        
    /**
     * MVC Event Listener
     *
     * @param MvcEvent $e
     */
    public function onRenderAddPathStacks(MvcEvent $e)
    {
        $r = $e->getResult();
        if (!$r instanceof ViewModel)
            return; // we don't get Renderer Result
        // allow viewScripts to append with others on content variable
        else $r->setAppend(true);

        // add path stacks
        $viewTemplatePathStack = $this->sm->get('ViewTemplatePathStack');
        foreach (array_reverse($this->pathStacks) as $path) { // child top and final theme must list last
            $viewTemplatePathStack->addPath($path);
        }
    }

    /**
     * Change layout
     *
     * @param MvcEvent $e
     */
    public function onRenderSpecLayout(MvcEvent $e)
    {
        $r = $e->getResult();
        if (! $r instanceof ViewModel || $r->terminate())
            return;

        $model = $e->getViewModel();

        $themeLocator  = $this->getThemeLocator();

        // we want theme path stack registered before
        #$this->onRenderAddPathStacks($e);

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
     * @throws \Exception
     * @return LocatorDefaultInterface
     */
    public function getThemeLocator()
    {
        if (! $this->themeLocator) {
            // use default theme locator to resolve theme object
            /** @var $defaultThemeLocator \yimaTheme\Theme\Locator */
            $defaultThemeLocator = $this->sm->get('yimaTheme.ThemeLocator');
            if (!$defaultThemeLocator instanceof LocatorDefaultInterface) {
                throw new \Exception(
                    'Default Theme Locator Service (yimaTheme.ThemeLocator) must instance of yimaTheme\Theme\LocatorDefaultInterface'
                );
            }
            $this->themeLocator = $defaultThemeLocator;
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
        $this->sm->setInvokableClass('yimaTheme.ThemeLocator', $themeLocator);

        $this->themeLocator = $themeLocator;

        return $this;
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
