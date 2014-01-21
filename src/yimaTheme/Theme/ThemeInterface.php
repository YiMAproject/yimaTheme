<?php
namespace yimaTheme\Theme;

interface ThemeInterface extends ThemeDefaultInterface
{
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
}