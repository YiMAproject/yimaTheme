<?php
namespace yimaTheme\Theme;

interface ThemeInterface
{
    /**
     * Get name of theme
     *
     * @return string
     */
    public function getName();

    /**
     * Get render layout name
     *
     * @internal param $name
     *
     * @return string
     */
    public function getTemplate();
}
