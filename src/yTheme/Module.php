<?php
namespace yTheme;

use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @var ModuleManagerInterface
     */
    protected $manager;

    public function init(ModuleManagerInterface $moduleManager)
    {
        //Theme Manager run before all on application bootstrap
        $events = $moduleManager->getEventManager()->getSharedManager();
        $events->attach(
            'Zend\Mvc\Application',
            MvcEvent::EVENT_BOOTSTRAP,
            array($this,'initThemeManager'),
            100000
        );
    }

    /**
     * Get Theme Manager Service and init them
     *
     * @param MvcEvent $e
     */
    public function initThemeManager(MvcEvent $e)
    {
        $sl = $e->getApplication()->getServiceManager();

        $themManager = $sl->get('ytheme\ThemeManager');
        if (!$themManager instanceof ManagerInterface) {
            throw new \Exception(
                sprintf('yTheme theme manager most instance of "ManagerInterface" but "%s" given.', get_class($themManager))
            );
        }

        $themManager->init($e);
    }

    /**
     * Register service on LOAD_MODULES_POST,
     * in service tavasote Manager dar event e BOOTSTRAP baraaie amaliaat dar dastres ast
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array (
            'invokables' => array (
                'yTheme\ThemeManager' => 'yTheme\Manager',
                'yTheme\ThemeLocator' => 'yTheme\Theme\Locator',
                'yTheme\ThemeObject'  => 'yTheme\Theme\Theme',
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

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
