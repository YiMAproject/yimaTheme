<?php
namespace yimaTheme\Theme;
use Poirot\Dataset\Entity;
use Zend\View\Model\ModelInterface;

/**
 * Interface ThemeDefaultInterface
 *
 * @package yimaTheme\Theme
 */
interface ThemeDefaultInterface extends
    ModelInterface,
    ThemeInterface
{
    /**
     * Initialize theme object if attained
     *
     * @return mixed
     */
    public function init();

    /**
     * Is theme object initialized?
     *
     * @return boolean
     */
    public function isInitialized();
    
    /**
     * not Final theme only bootstraped and adding pathStack
     * then fallBack to theme resolver till resolve Final 
     * 
     * @return boolean
     */
    public function isFinalTheme();

    /**
     * Set Final Theme Flag
     *
     * @param bool $bool Final Theme Flag
     *
     * @return $this
     */
    public function setFinalTheme($bool = true);

    /**
     * Set name of theme
     *
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name);

    /**
     * Set dir to folder that store themes
     *
     * @param string $path dir path
     *
     * @return mixed
     */
    public function setThemesPath($path);

    /**
     * Path to themes folder
     *
     * @return string
     */
    public function getThemesPath();

    /**
     * Get Theme Config Object Entity
     *
     * @return Entity
     */
    public function config();
}
