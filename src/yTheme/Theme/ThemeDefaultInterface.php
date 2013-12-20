<?php
namespace yTheme\Theme;

interface ThemeDefaultInterface
{
    /**
     * Set name of theme
     *
     * @param string $name
     *
     * @return mixed
     */
    public function setName($name);

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