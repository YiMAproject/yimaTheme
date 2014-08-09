<?php
namespace yimaTheme\Theme;

use yimaTheme\Theme\Theme as ThemeObject;

/**
 * Interface LocatorInterface
 *
 * @package yimaTheme\Theme
 */
interface LocatorInterface
{
    /**
     * Find Matched Theme and return object
     *
     * @return ThemeObject|false
     */
    public function getPreparedThemeObject();
}
