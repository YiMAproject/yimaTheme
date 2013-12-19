<?php
namespace yTheme\Theme;

use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use yTheme\Theme\LocatorInterface;
use yTheme\Theme\Options;

use yTheme\Resolvers\Aggregate;
use yTheme\Resolvers\ConfigAwareInterface;
use yTheme\Resolvers\LocatorAwareInterface;
use yTheme\Resolvers\EventAwareInterface;

// we want serviceManager Injected into
use Zend\ServiceManager;
use Zend\ServiceManager\Config as ServiceConfig;

class Locator implements
    LocatorInterface,
    ServiceManager\ServiceLocatorAwareInterface
{
    /**
     * Name of template
     *
     * @var string
     */
    protected $name;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;

    protected $initialized = false;

    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $options = $this->getOptions()->toArray();

        // autoload
        if (is_array($options['autoloader'])) {
            \Zend\Loader\AutoloaderFactory::factory($options['autoloader']);
        }

        // configure services registered in serviceManager
        $services = array();
        foreach ($options as $key=>$val) {
            if ($this->getServiceLocator()->has($key)) {
                // theme config key is a registered service
                $service = $this->getServiceLocator()->get($key);
                if ($service instanceof ServiceManager\ServiceLocatorInterface) {
                    $services[] = $key;
                    $serviceConfig = new ServiceConfig($val);
                    $serviceConfig->configureServiceManager($service);
                }
            }
        }

        // merge theme options to application merged config
        unset($options['autoloader']);

        foreach ($options as $key=>$val) {
            if ( in_array($key, $services) ) {
                unset($options[$key]);
                continue;
            }

            $callSetMethod = 'set'. str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this->getOptions(), $callSetMethod)) {
                unset($options[$key]);
            }
        }

        $serviceManager = $this->getServiceLocator();
        $mergdConf = $serviceManager->get('Config');
        $config = ArrayUtils::merge($mergdConf, $options);

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('config',$config);
        $serviceManager->setAllowOverride(false);

        $this->initialized = true;
        return $this;
    }

    /**
     * Ba tavajoh be resolver haa dar config, naame theme raa
     * tashkhis midahad.
     *
     * @return bool|string
     * @throws \Exception
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        // get template name {
        $config = $this->getModuleConfig();
        if (!isset($config['theme_resolver_adapter'])) {
            throw new \Exception('No resolver adapter defined for cTheme.');
        } else {
            $config = $config['theme_resolver_adapter'];
            if (!is_array($config)) {
                if ($config instanceof \Iterator) {
                    $config = ArrayUtils::iteratorToArray($config);
                }

                // is string
                if (is_string($config)) {
                    $config = array(
                        "{$config}" => 1
                    );
                }
            }
        }

        $nameResolver = new Aggregate();
        foreach ($config as $service=>$priority) {
            if ($this->getServiceLocator()->has($service)) {
                $service = $this->getServiceLocator()->get($service);
            } else {
                if (!class_exists($service)) {
                    throw new \Exception("Resolver '$service' not found for yTheme.");
                }

                $service = new $service();
            }

            if ($service instanceof ConfigAwareInterface) {
                // set cTheme config for resolver
                $service->setConfig($this->getModuleConfig());
            }

            $nameResolver->attach($service,$priority);
        }

        $themeName = $nameResolver->getName();

        if (empty($themeName) && ! ($themeName === '0') ) {
            /**
             * @TODO attention or log to developer
             */

            return false;
            //throw new \Exception('Can`t resolve to theme name.');
        }
        // ... }

        return $this->name = $themeName;
    }

    /**
     * Name of layout to render.
     * It get by Manager and inject into MVC
     *
     * @return mixed
     */
    public function getLayout()
    {
        $config = $this->getModuleConfig();
        if (isset($config['layout_resolver_adapter'])) {
            $config = $config['layout_resolver_adapter'];
            if (! is_array($config) ) {
                if ($config instanceof \Iterator) {
                    $config = ArrayUtils::iteratorToArray($config);
                }

                // is string
                if (is_string($config)) {
                    $config = array(
                        "{$config}" => 1
                    );
                }
            }
        }

        $nameResolver = new Aggregate();
        foreach ($config as $service=>$priority) {
            if ($this->getServiceLocator()->has($service)) {
                $service = $this->getServiceLocator()->get($service);
            } else {
                if (!class_exists($service)) {
                    throw new \Exception("Layout Resolver '$service' not found for yTheme.");
                }

                $service = new $service();
            }

            if ($service instanceof ConfigAwareInterface) {
                // set cTheme config for resolver
                $service->setConfig($this->getModuleConfig());
            }

            if ($service instanceof LocatorAwareInterface) {
                // set cTheme config for resolver
                $service->setLocator($this);
            }

            if ($service instanceof EventAwareInterface) {
                // set cTheme config for resolver
                $e = $this->getOptions()->getParam('MvcEvent');
                if (!$e instanceof MvcEvent ) {
                    throw new \Exception('MvcEvent need for layout resolver but not exists.');
                }
                $service->setEvent($e);
            }

            $nameResolver->attach($service,$priority);
        }

        $layout = $nameResolver->getName();

        if (empty($layout) && ! ($layout === '0') ) {
            return;
        }

        return $layout;
    }

    /**
     * Masir folder e theme raaa bar migardaanad
     *
     * @return string
     */
    public function getPathName()
    {
        $themeName = $this->getName();

        $config = $this->getModuleConfig();
        // get default directory path to themes folder {
        if (isset($config['themes_default_path'])) {
            $themesDefaultPath = $config['themes_default_path'];
        }

        $path = (isset($themesDefaultPath)) ? $themesDefaultPath .DS. $themeName : false;
        // ... }

        // get theme specify path, use case in modules that present a specify theme inside, like admin panel.
        if (isset($config['themes']) && is_array($config['themes'])
            && isset($config['themes'][$themeName]))
        {
            if (array_key_exists('dir_path',$config['themes'][$themeName])) {
                $path = $config['themes'][$themeName]['dir_path'];
            }
        }

        return realpath($path);
    }

    /**
     * get yTheme Config merged with options config
     *
     * @return Options
     */
    public function getOptions()
    {
        if (! $this->options) {
            $this->options = new Options($this);
        }

        return $this->options;
    }

    /**
     * Mostaghiman file e marboot be option haaie theme raa mikhaanad
     *
     * @return array
     */
    protected function getOptionsFromFile()
    {
        $themeConf = array();

        $configFile = rtrim($this->getPathName(), DS).DS.'theme.config.php';
        if (file_exists($configFile))
        {
            $themeConf = include $configFile;
            $themeConf = (is_array($themeConf)) ? $themeConf : array();
        }

        return $themeConf;
    }

    /**
     * get yTheme Config merged with options config
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getModuleConfig()
    {
        $sm = $this->getServiceLocator();

        $config = $sm->get('config');
        if (! (isset($config['yima-ytheme']) && is_array($config['yima-ytheme'])) ) {
            throw new \Exception('Not any configuration found for yTheme');
        }

        return $config['yima-ytheme'];
    }

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