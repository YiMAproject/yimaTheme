<?php
namespace cThemes;
use cThemes\Theme\LocatorInterface;

interface ManagerInterface
{
    public function setThemeLocator(LocatorInterface $locator);

    public function getThemeLocator();
}