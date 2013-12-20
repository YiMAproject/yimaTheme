<?php
namespace yTheme\Theme;

interface LocatorDefaultInterface extends LocatorInterface
{
    /**
     * Set theme_locator Config for default Locators
     *
     * @param array $config Merged config used by locator
     *
     * @return mixed
     */
    public function setConfig($config);

    public function getConfig();
}