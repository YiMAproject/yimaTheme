<?php
namespace yimaTheme\View\Helper;

use yimaTheme\Theme\LocatorDefaultInterface;
use yimaTheme\Theme\ThemeDefaultInterface;
use Zend\View\Helper\AbstractHelper;

class ThemeHelper extends AbstractHelper
{
    /**
     * @var LocatorDefaultInterface
     */
    protected $themeLocator;

    /**
     * @var ThemeDefaultInterface
     */
    protected $themeObject;

    /**
     * Constructor
     *
     * @param LocatorDefaultInterface $themeLocator
     */
    public function __construct(LocatorDefaultInterface $themeLocator)
    {
        $this->themeLocator = $themeLocator;

        $this->themeObject  = $themeLocator->getTheme();
    }

    public function __invoke()
    {
        return $this;
    }

    /**
     * Get Instance To Theme Object
     *
     * @return \yimaTheme\Theme\Theme|ThemeDefaultInterface
     */
    public function getThemeInstance()
    {
        return $this->themeObject;
    }

    /**
     * Return configs for a theme name
     *
     */
    public function getConfigs()
    {
        $config = $this->themeLocator->getConfig();
        $config = (isset($config['themes'])) ? $config['themes'] : array();

        $themeName = $this->themeObject->getName();

        return isset($config[$themeName]) ? $config[$themeName] : array();
    }

    public function getConfig($name)
    {
        $config = $this->getConfigs();

        return (isset($config[$name])) ? $config[$name] : false;
    }
}
