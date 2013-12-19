<?php
namespace yTheme\Resolvers;

/**
 * Resolver classes that implement this can have yTheme configuration injected into
 *
 * Class ConfigAwareInterface
 * @package yTheme\Resolvers
 */
interface ConfigAwareInterface
{
    /**
     * Set yTheme merged config
     *
     * @param Array $config
     */
    public function setConfig(array $config);

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig();
}
