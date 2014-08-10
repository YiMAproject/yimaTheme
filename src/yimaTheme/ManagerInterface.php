<?php
namespace yimaTheme;
use yimaTheme\Theme\ThemeInterface;

/**
 * Interface ManagerInterface
 *
 * @package yimaTheme
 */
interface ManagerInterface
{
    /**
     * Init Theme Manager To Work
     *
     * @return mixed
     */
    public function init();

    /**
     * Get Theme Object
     * : share across application by helpers
     *
     * @return ThemeInterface
     */
    public function getThemeObject();
}
