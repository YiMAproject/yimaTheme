<?php
namespace yimaTheme\Theme;

use Poirot\Core\Entity;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * Class Theme
 * @package yimaTheme\Theme
 */
class Theme extends ViewModel
    implements
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
     * dir path that store themes

     * @var string
     */
    protected $themesPath;

    protected $initialized = false;

    /**
     * not Final theme only initialized and adding pathStack
     * then fallBack to theme resolver till resolve Final 
     * 
     * @var boolean
     */
    protected $isFinal = true;

    /**
     * @var Parent Theme Object
     */
    protected $parent;

    /**
     * @var Entity Theme Configs Entity
     */
    protected $config;

    /**
     * @var ServiceManager
     */
    protected $serviceLocator;

    /**
     * Constructor
     *
     * @param null|string $name Name of theme
     * @param null|string $themesPath Path dir to themes folder
     */
    public function __construct($name = null, $themesPath = null)
    {
        if ($name !== null)
            $this->setName($name);

        if ($themesPath !== null)
            $this->setThemesPath($themesPath);
    }

    /**
     * Initialize theme object if attained
     *
     * @throws \Exception
     * @return mixed
     */
    public function init()
    {
        if ($this->isInitialized())
            return $this;

        if (!$this->getThemesPath() || !$this->getName())
            throw new \Exception('Theme Cant initialize because theme name or theme paths not present.');

        $themePathname = $this->getThemesPath().DS.$this->getName();
        if (!is_dir($themePathname))
            throw new \Exception(sprintf('Theme "%s" not found in "%s".', $this->getName(), $themePathname));

        $bootstrap    = $themePathname.DS.'theme.bootstrap.php';
        if (file_exists($bootstrap)) {
            ob_start();
            set_error_handler(
                function($errno, $errstr) {
                    throw new \ErrorException($errstr, $errno);
                },
                E_ALL
            );

            include $bootstrap; // Bootstrap Theme

            restore_error_handler();
            ob_get_clean();
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
     * not Final theme only initialized and adding pathStack
     * then fallBack to theme resolver till resolve Final 
     * 
     * @return boolean
     */
    public function isFinalTheme()
    {
        return $this->isFinal;
    }

    /**
     * Set Final Theme Flag
     *
     * @param bool $bool Final Theme Flag
     *
     * @return $this
     */
    public function setFinalTheme($bool = true)
    {
        $this->isFinal = (boolean) $bool;

        return $this;
    }

    /**
     * Add a child model
     *
     * @param  ModelInterface $child
     * @param  null|string    $captureTo Optional; if specified, the "capture to" value to set on the child
     * @param  null|bool      $append    Optional; if specified, append to child  with the same capture
     *
     * @return ViewModel
     */
    public function addChild(ModelInterface $child, $captureTo = null, $append = null)
    {
        parent::addChild($child, $captureTo, $append);

        if ($child instanceof ThemeDefaultInterface) {
            $child->parent = $this;
        }

        return $this;
    }

    /**
     * Get Parent Theme
     *
     * @return ThemeDefaultInterface
     */
    public function getParentTheme()
    {
        if ($this->isFinalTheme()) {
            // Final Theme can't have parent theme
            return false;
        }

        return $this->parent;
    }

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
     * Set dir to folder that store themes
     *
     * @param string $path dir path
     *
     * @throws \Exception
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
     * Get Theme Config Object Entity
     *
     * @throws \Exception
     * @return Entity
     */
    public function config()
    {
        if (!$this->config) {
            $config = array();

            $configFile = $this->getThemesPath()
                .DIRECTORY_SEPARATOR.$this->getName()
                .DIRECTORY_SEPARATOR.'theme.config.php';
            if (file_exists($configFile)) {
                ob_start();
                set_error_handler(
                    function($errno, $errstr) {
                        throw new \ErrorException($errstr, $errno);
                    },
                    E_ALL
                );

                $config = include $configFile;

                restore_error_handler();
                ob_get_clean();

                if (!is_array($config))
                    throw new \Exception('Invalid "'.$this->getName().'" Theme Config File. It must return array.');
            }

            $this->config = new Entity($config);
        }

        return $this->config;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
