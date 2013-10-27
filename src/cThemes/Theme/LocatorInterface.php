<?php
namespace cThemes\Theme;

interface LocatorInterface
{
    /**
     * naame theme raa bar migardaanad
     *
     * @return bool|string
     */
    public function getName();

    /**
     * Masir folder e theme raaa bar migardaanad
     *
     * @return string
     */
    public function getPathName();


    /**
     * Name of layout to render.
     * It get by Manager and inject into MVC
     *
     * @return mixed
     */
    public function getLayout();

}