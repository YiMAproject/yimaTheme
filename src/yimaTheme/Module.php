<?php
namespace yimaTheme;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

/**
 * Class Module
 * @package yimaTheme
 */
class Module implements
    InitProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface,
    ConfigProviderInterface,
    AutoloaderProviderInterface
{
    /**
     * @var ModuleManagerInterface
     */
    protected $manager;

    /**
     * Initialize workflow
     *
     * @param \Zend\ModuleManager\ModuleManagerInterface $moduleModuleManager
     * @internal param \Zend\ModuleManager\ModuleManagerInterface $manager
     * @return void
     */
    public function init(ModuleManagerInterface $moduleModuleManager)
    {
        $events = $moduleModuleManager->getEventManager();
        $events->attach(
            ModuleEvent::EVENT_LOAD_MODULES_POST,
            array($this,'initThemeManager'),
            -100000
        );

        $events->attach(
            ModuleEvent::EVENT_LOAD_MODULES_POST,
            array($this,'onLoadModulesPostAddServices'),
            -100000
        );
    }

    /**
    * @param ModuleEvent $e
    */
    public function onLoadModulesPostAddServices(ModuleEvent $e)
    {
        /** @var $moduleManager \Zend\ModuleManager\ModuleManager */
        $moduleManager = $e->getTarget();
        // $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        /** @var $sm ServiceManager */
        $sm      = $moduleManager->getEvent()->getParam('ServiceManager');
        $sm->setInvokableClass('yimaTheme\ThemeObject', 'yimaTheme\Theme\Theme', false);
    }

    /**
     * Get Theme Manager Service and init them
     *
     * @param ModuleEvent $e
     * @throws \Exception
     */
    public function initThemeManager(ModuleEvent $e)
    {
        /** @var $moduleManager \Zend\ModuleManager\ModuleManager */
        $moduleManager = $e->getTarget();
        // $sharedEvents = $moduleManager->getEventManager()->getSharedManager();

        $sm = $moduleManager->getEvent()->getParam('ServiceManager');
        $themManager = $sm->get('yimaTheme.Manager');
        if (!$themManager instanceof ManagerInterface) {
            throw new \Exception(
                sprintf('yimaTheme theme manager most instance of "ManagerInterface" but "%s" given.', get_class($themManager))
            );
        }

        $themManager->init();
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return array (
            'invokables' => array (
                'yimaTheme.Manager' => 'yimaTheme\Manager',
                    'yimaTheme\ThemeManager\ListenerAggregate' => 'yimaTheme\Manager\DefaultListenerAggregate',
                'yimaTheme\ThemeLocator' => 'yimaTheme\Theme\Locator',
                    // because of this is shared serv. and with cloning problem -
                    // (we have to reset each objects).
                    // this model service set with service method with share by -
                    // default set to false
                    // on init module
                    # 'yimaTheme\ThemeObject'  => 'yimaTheme\Theme\Theme',
            ),
        );
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
                'theme' => 'yimaTheme\View\Helper\ThemeHelper',
            ),
        );
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Return an array for passing to Zend\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

}
