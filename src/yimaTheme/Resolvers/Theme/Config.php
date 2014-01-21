<?php
namespace yimaTheme\Resolvers\Theme;

use yimaTheme\Resolvers\ConfigResolverAwareInterface;
use yimaTheme\Resolvers\ResolverInterface;

class Config implements
    ResolverInterface,
    ConfigResolverAwareInterface
{
    /**
     * @var array
     */
    protected $config;

    public function getName()
    {
        $name = false;

        $config = $this->config;
        if (isset($config['theme_locator']) && is_array($config['theme_locator'])) {

            $name = (isset($config['theme_locator']['default_theme_name']))
                ? $config['theme_locator']['default_theme_name']
                : false;
        }

        return $name;
    }

    /**
     * Set yimaTheme merged config
     *
     * @param Array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}