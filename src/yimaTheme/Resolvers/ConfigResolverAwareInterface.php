<?php
namespace yimaTheme\Resolvers;

/**
 * Resolver classes that implement this can have yimaTheme configuration injected into
 *
 * Class ConfigAwareInterface
 * @package yimaTheme\Resolvers
 */
interface ConfigResolverAwareInterface
{
    /**
     * Set yimaTheme merged config
     *
     * @param Array $config
     */
    public function setConfig(array $config);
}
