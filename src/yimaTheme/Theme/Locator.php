<?php
namespace yimaTheme\Theme;

use yimaTheme\Manager;
use yimaTheme\Resolvers;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager;

class Locator implements
    LocatorDefaultInterface,
    ServiceManager\ServiceLocatorAwareInterface
{
    /**
     * Name of template
     *
     * @var string
     */
    protected $name;

    /**
     * @var ServiceManager\ServiceManager
     */
    protected $serviceManager;
    
    /**
     * @var array[Resolvers\Aggregate] Resolvers Aggregate
     */
    protected $resolverObject = array();

    /**
     * Find Matched Theme and return object
     *
     * @return Theme|false
     */
    public function getPreparedThemeObject()
    {
        $name = $this->attainThemeName();
        $path = $this->attainPathName();
        
        $return = false;
        if ($name && $path) {
            // we are attained theme
            $return = $this->getThemeObject();
        	$return->setName($name);
        	$return->setThemesPath($path);
        }
        
        return $return;
    }

    /**
     * Get ThemeObject
     * : it must be unique new instance object theme -
     *   on each get request
     *
     * @return Theme
     * @throws \Exception
     */
    protected function getThemeObject()
    {
        $themeObject = $this->getServiceLocator()
            ->get('yimaTheme.ThemeObject');
        if (! $themeObject instanceof ThemeDefaultInterface) {
            throw new \Exception(
                sprintf(
                    'yimaTheme.ThemeObject must instanceof "\yimaTheme\Theme\ThemDefaultInterface" but "%s" given.',
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
     * @throws \Exception
     * @return string | false
     */
    public function getMvcLayout(MvcEvent $e)
    {
        try {
            $resolver = $this->getResolverObject('mvclayout_resolver_adapter', array('event_mvc' => $e));
        } catch (\Exception $e) {
            throw $e;
        }

        $layout = $resolver->getName();
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
        $themeName = $this->getResolverObject('resolver_adapter_service')
            ->getName();

        return (empty($themeName) && ! ($themeName === '0')) ? false : $themeName;
    }
    
    /**
     * Get Resolver Object used by Locator
     * 
     * @param string $state   Setup configuration resolver
     * @param array  $options Options
     * @return Resolvers\Aggregate
     * @throws \Exception
     */
    public function getResolverObject($state = null, array $options = array())
    {
        if ($state == null && isset($this->resolverObject['last_resolver'])) {
            // latest invoked resolver
            return $this->resolverObject['last_resolver'];
        }
        
        if ($state != 'resolver_adapter_service' && $state != 'mvclayout_resolver_adapter')
            throw new \Exception('Invalid state name provided.');
        
        // create resolver state object from config
        $config = $this->getConfig();

        if (isset($config['theme_locator']))
            $config = $config['theme_locator'];
        else
            $config = array();

        if (!isset($config[$state]))
            throw new \Exception("Theme Resolver Service not present in config[$state].");

        $config = $config[$state];
        // is string, 'resolver_adapter' => 'resolver\service'
        if (is_string($config)) {
            $config = array(
                "{$config}" => 1
            );
        }

        if (isset($this->resolverObject[$state])) {
            $resolver = $this->resolverObject[$state];

            $this->resolverObject['last_resolver'] = $resolver;

            return $resolver;
        }
        else 
            $resolver = new Resolvers\Aggregate();
        
        foreach ($config as $service => $priority)
        {
            if ($this->getServiceLocator()->has($service)) {
                $service = $this->getServiceLocator()->get($service);
            } else {
                if (!class_exists($service))
                    throw new \Exception("Resolver '$service' not found for yimaTheme as Service either Class.");

                $service = new $service();
            }

            if ($service instanceof Resolvers\LocatorResolverAwareInterface) {
                // inject themeLocator to access config and other things by resolver
                $service->setThemeLocator($this);
            }

            if ($service instanceof Resolvers\ConfigResolverAwareInterface) {
                // set yimaTheme config for resolver
                $service->setConfig($this->getConfig());
            }
            
            if (isset($options['event_mvc']))
                if ($service instanceof Resolvers\MvcResolverAwareInterface)
                	$service->setMvcEvent($options['event_mvc']);

            $resolver->attach($service, $priority);
        }
        
        $this->resolverObject[$state]          = $resolver;
        $this->resolverObject['last_resolver'] = $resolver;
        
        return $resolver;
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

