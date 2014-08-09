<?php
namespace yimaTheme\Theme;

/**
 * Interface ThemeDefaultInterface
 *
 * @package yimaTheme\Theme
 */
interface ThemeDefaultInterface extends ThemeInterface
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
     * Set name of theme
     *
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name);

    /**
     * Set render layout name
     *
     * @param $name
     *
     * @return mixed
     */
    public function setLayout($name);

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

    /*public function render($name);*/

    /**
     * Used for passing some params variable between each action during MvcEvents.
     * absolutely you can use to send param(s) to theme object
     *
     *  zmani hast ke manager ehtiaj daarad maghaadiri raa be locator ersaal konad
     *  va nesbat be aaan amaliaat anjaam shavad, masalan hengaame render MvcEvent
     *  ehtiaj ast baraaie shenaakhte layout.
     *
     * @param $name
     * @param $value
     */
    public function setParam($name, $value);

    public function getParam($name);
}