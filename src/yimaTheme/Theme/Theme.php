<?php
namespace yimaTheme\Theme;

use Poirot\Dataset\Entity;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class Theme
 * @package yimaTheme\Theme
 */
class Theme implements
    ThemeDefaultInterface,
    ServiceLocatorAwareInterface
{
    /**
     * Theme name
     *
     * @var string
     */
    protected $name;

    /**
     * Layout name for render
     *
     * @var string
     */
    protected $layout;

    /**
     * @var array
     */
    protected $params;

    /**
     * dir path that store themes

     * @var string
     */
    protected $themesPath;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    protected $initialized = false;

    /**
     * not Final theme only bootstraped and adding pathStack
     * then fallBack to theme resolver till resolve Final 
     * 
     * @var boolean
     */
    protected $isFinal = true;

    /**
     * @var ThemeDefaultInterface Child Theme
     */
    protected $child;

    /**
     * @var Entity Theme Options Entity
     */
    protected $options;

    /**
     * Constructor
     *
     * @param null|string $name Name of theme
     * @param null|string $themesPath Path dir to themes folder
     */
    public function __construct($name = null, $themesPath = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }

        if ($themesPath !== null) {
            $this->setThemesPath($themesPath);
        }
    }

    /**
     * Initialize theme object if attained
     *
     * @return mixed
     */
    public function init()
    {
        if ($this->isInitialized()) {
            return $this;
        }

        if (!$this->getThemesPath() || !$this->getName()) {
            throw new \Exception('Theme Cant initialize because theme name or theme paths not present.');
        }

        $themePathname = $this->getThemesPath().DS.$this->getName();
        if (!is_dir($themePathname))
            throw new \Exception(sprintf('Theme "%s" not found in "%s".', $this->getName(), $themePathname));

        $configFile    = $themePathname.DS.'theme.bootstrap.php';
        if (file_exists($configFile)) {
            include $configFile;
        }

        $this->initialized = true;

        return $this;
    }

    /**
     * Is theme object attained theme and initialized?
     * : attained theme mean having theme name
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }
        
    /**
     * not Final theme only bootstraped and adding pathStack
     * then fallBack to theme resolver till resolve Final 
     * 
     * @return boolean
     */
    public function isFinalTheme()
    {
        return $this->isFinal;
    }

    // --- implemented methods ---------------------------------------------------------------------------------------------------

    /**
     * Set name of theme
     *
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Set render layout name
     *
     * @param $name
     *
     * @return $this
     */
    public function setLayout($name)
    {
        $this->layout = (string) $name;

        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set dir to folder that store themes
     *
     * @param string $path dir path
     *
     * @return mixed
     */
    public function setThemesPath($path)
    {
        if (!is_dir($path)) {
            throw new \Exception(
                sprintf('Path "%s" not found.', $path)
            );
        }

        $this->themesPath = rtrim($path, DS);

        return $this;
    }

    public function getThemesPath()
    {
        return $this->themesPath;
    }

    /**
     * Set Child Theme
     *
     * @param ThemeDefaultInterface $theme
     *
     * @return $this
     */
    public function setChild(ThemeDefaultInterface $theme)
    {
        $this->child = $theme;

        return $this;
    }

    /**
     * Has Child Theme ?
     *
     * @return boolean
     */
    public function hasChild()
    {
        return (isset($this->child) && $this->child instanceof ThemeDefaultInterface);
    }

    /**
     * Get Child Theme
     *
     * @return ThemeDefaultInterface
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Get Options Object Entity
     *
     * @return Entity
     */
    public function options()
    {
        if (!$this->options) {
            $this->options = new Entity();
        }

        return $this->options;
    }

    /**
     * Used for passing some params variable between each action during MvcEvents.
     *
     *  zmani hast ke manager ehtiaj daarad maghaadiri raa be locator ersaal konad
     *  va nesbat be aaan amaliaat anjaam shavad, masalan hengaame render MvcEvent
     *  ehtiaj ast baraaie shenaakhte layout.
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $name = strtolower($name);

        $this->params[$name] = $value;

        return $this;
    }

    public function getParam($name, $default = null)
    {
        $name = strtolower($name);

        return (isset($this->params[$name])) ? $this->params[$name] : $default;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceManager = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceManager;
    }
}
