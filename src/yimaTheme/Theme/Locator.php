<?php
namespace yimaTheme\Theme;

use yimaTheme\Manager;
use yimaTheme\ManagerInterface;
use yimaTheme\Resolvers\ConfigResolverAwareInterface;
use yimaTheme\Resolvers\LocatorResolverAwareInterface;
use yimaTheme\Resolvers\MvcResolverAwareInterface;
use Zend\Mvc\MvcEvent;

use yimaTheme\Resolvers\Aggregate;

use Zend\ServiceManager;

class Locator implements
    LocatorDefaultInterface,
    ServiceManager\ServiceLocatorAwareInterface
{
    /**
     * Default Manager theme_locator config
     *
     * @var array
     */
    protected $config;

    /**
     * @var
     */
    protected $themeObject;

    /**
     * Name of template
     *
     * @var string
     */
    protected $name;

    /**
     * Injected Theme Manager Instance Object
     *
     * @var Manager
     */
    protected $themeManager;

    /**
     * @var ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * Find Matched Theme and return object
     *
     * @return Theme
     */
    public function getPreparedThemeObject()
    {
        $themeObject = $this->getThemeObject();

        $name = $this->attainThemeName();
        if ($name) {
            $themeObject->setName($name);
            $themeObject->setThemesPath($this->attainPathName());

            $themeObject->init();
        }

        return $themeObject;
    }

    /**
     * Get ThemeObject
     *
     * @return Theme
     * @throws \Exception
     */
    protected function getThemeObject()
    {
        $themeObject = $this->getServiceLocator()
            ->get('yimaTheme\ThemeObject');
        if (! $themeObject instanceof ThemeDefaultInterface) {
            throw new \Exception(
                sprintf(
                    'yimaTheme\ThemeObject must instanceof "\yimaTheme\Theme\ThemDefaultInterface" but "%s" given.',
                    get_class($themeObject)
                )
            );
        }

        return $themeObject;
    }

    /**
     * Get layout name according to MvcEvent on EVENT_DISPATCH
     *
     * @param MvcEvent $e
     *
     * @return string | false
     */
    public function getMvcLayout(MvcEvent $e)
    {
        $config = $this->getConfig();
        if (isset($config['theme_locator'])) {
            $config = $config['theme_locator'];
        } else {
            $config = array();
        }

        if (isset($config['mvclayout_resolver_adapter'])) {
            $config = $config['mvclayout_resolver_adapter'];
        } else {
            // use default layout name
            return false;
        }

        // is string
        if (is_string($config)) {
            $config = array(
                "{$config}" => 1
            );
        }

        $nameResolver = new Aggregate();
        foreach ($config as $service=>$priority) {
            if ($this->getServiceLocator()->has($service)) {
                $service = $this->getServiceLocator()->get($service);
            } else {
                if (!class_exists($service)) {
                    throw new \Exception("Layout Resolver '$service' not found for yimaTheme.");
                }

                $service = new $service();
            }

            if ($service instanceof LocatorResolverAwareInterface) {
                // inject themeLocator to access config and other things by resolver
                $service->setThemeLocator($this);
            }

            if ($service instanceof ConfigResolverAwareInterface) {
                // set yimaTheme config for resolver
                $service->setConfig($this->getConfig());
            }

            if ($service instanceof MvcResolverAwareInterface) {
                $service->setMvcEvent($e);
            }

            $nameResolver->attach($service,$priority);
        }

        $layout = $nameResolver->getName();

        if (empty($layout) && ! ($layout === '0') ) {
            return false;
        }

        return $layout;
    }


    /**
     * Resolve to theme name by Aggregate services
     *
     * @return bool
     * @throws \Exception
     */
    protected function attainThemeName()
    {
        $config = $this->getConfig();

        if (isset($config['theme_locator'])) {
            $config = $config['theme_locator'];
        } else {
            $config = array();
        }

        if (!isset($config['resolver_adapter_service'])) {
            throw new \Exception('Theme Resolver Service not present in config[resolver_adapter_service].');
        }

        $config = $config['resolver_adapter_service'];
        // is string, 'resolver_adapter' => 'resolver\service'
        if (is_string($config)) {
            $config = array(
                "{$config}" => 1
            );
        }

        $nameResolver = new Aggregate();
        foreach ($config as $service => $priority)
        {
            if ($this->getServiceLocator()->has($service)) {
                $service = $this->getServiceLocator()->get($service);
            } else {
                if (!class_exists($service)) {
                    throw new \Exception("Resolver '$service' not found for yimaTheme.");
                }

                $service = new $service();
            }

            if ($service instanceof LocatorResolverAwareInterface) {
                // inject themeLocator to access config and other things by resolver
                $service->setThemeLocator($this);
            }

            if ($service instanceof ConfigResolverAwareInterface) {
                // set yimaTheme config for resolver
                $service->setConfig($this->getConfig());
            }

            $nameResolver->attach($service,$priority);
        }

        $themeName = $nameResolver->getName();

        return (empty($themeName) && ! ($themeName === '0')) ? false : $themeName;
    }

    /**
     * Get themes folder dir from config
     *
     * @return string
     */
    protected function attainPathName()
    {
        $path = false;

        // get default themes path by config {
        $config = $this->getConfig();
        if (isset($config['theme_locator']['themes_default_path'])) {
            $path = $config['theme_locator']['themes_default_path'];
        }
        // ... }

        // get theme specify path,
        // use case in modules that present a specify theme inside, like admin panel.
        $themeName = $this->attainThemeName();
        if (isset($config['themes']) && is_array($config['themes'])
            && isset($config['themes'][$themeName]))
        {
            if (array_key_exists('dir_path',$config['themes'][$themeName])) {
                $path = $config['themes'][$themeName]['dir_path'];
            }
        }

        return $path;
    }

    /**
     * Get default Manager theme_locator config
     *
     * @return array
     */
    protected function getConfig()
    {
        // get default manager config used by default theme locator
        $config = $this->getServiceLocator()->get('config');
        if (isset($config['yima-theme']) && is_array($config['yima-theme'])) {
            $config = $config['yima-theme'];
        } else {
            $config = array();
        }

        return $config;
    }

    // -- implement methods ----------------------------------------------------------------------------------------------------------

    /**
     * Set service locator
     *
     * @param ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceManager;
    }
}

