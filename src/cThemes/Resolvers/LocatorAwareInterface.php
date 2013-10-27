<?php
namespace cThemes\Resolvers;

use cThemes\Theme\LocatorInterface;

/**
 * Resolver classes that implement this can have Locator injected into
 *
 * Class LocatorAwareInterface
 * @package cThemes\Resolvers
 */
interface LocatorAwareInterface
{
    /**
     *
     * @param LocatorInterface $event
     */
    public function setLocator(LocatorInterface $event);

    /**
     *
     * @return LocatorInterface
     */
    public function getLocator();
}
