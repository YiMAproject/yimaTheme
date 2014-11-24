<?php
namespace yimaTheme\Resolvers\Theme;

use yimaTheme\Resolvers\ResolverInterface;

class Sentenced implements
    ResolverInterface
{
    /**
     * @var string Theme
     */
    public $theme;

    /**
     * @var string Default Theme
     */
    static protected $default_theme = 'default';

    function getName()
    {
        return ($this->theme) ? :self::$default_theme;
    }

    /**
     * Set Default Template Name
     *
     * @param string $name Template Name
     */
    static function setDefault($name)
    {
        self::$default_theme = (string) $name;
    }
}
