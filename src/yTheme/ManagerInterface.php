<?php
namespace yTheme;
use yTheme\Theme\LocatorInterface;

interface ManagerInterface
{
    public function setThemeLocator(LocatorInterface $locator);

    public function getThemeLocator();
}