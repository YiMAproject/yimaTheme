<?php
namespace yimaTheme\Theme;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * Class Theme
 * @package yimaTheme\Theme
 */
class Theme implements
    ThemeInterface,
    ServiceManagerAwareInterface
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
     * Initialize theme
     *
     * initialize by theme locator on instancing theme object
     */
    public function initialize()
    {
        if ($this->initialized) {
            return $this;
        }

        $themePathname = $this->getThemesPath().DS.$this->getName();
        $configFile = $themePathname.DS.'theme.bootstrap.php';
        if (file_exists($configFile)) {
            include $configFile;
        }

        $this->initialized = true;

        return $this;
    }

    /**
     * Mostaghiman file e marboot be option haaie theme raa mikhaanad
     * @return array
     * @deprecated
     */
    protected function getOptions()
    {
        $themeConf = array();

        $themePathname = $this->getThemesPath().DS.$this->getName();
        $configFile = $themePathname.DS.'theme.config.php';
        if (file_exists($configFile)) {
            $themeConf = include $configFile;
            $themeConf = (is_array($themeConf)) ? $themeConf : array();
        }

        return $themeConf;
    }

    // implemented methods --------------------------------------------------\

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

    public function getParam($name)
    {
        $name = strtolower($name);

        return (isset($this->params[$name])) ? $this->params[$name] : false;
    }


    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Get service manager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}