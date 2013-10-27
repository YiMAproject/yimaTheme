<?php
namespace cThemes\Resolvers;

/**
 * Resolver classes that implement this can have cThemes configuration injected into
 *
 * Class ConfigAwareInterface
 * @package cThemes\Resolvers
 */
interface ConfigAwareInterface
{
    /**
     * Set cThemes merged config
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
