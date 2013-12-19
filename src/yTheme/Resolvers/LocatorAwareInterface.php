<?php
namespace yTheme\Resolvers;

use yTheme\Theme\LocatorInterface;

/**
 * Resolver classes that implement this can have Locator injected into
 *
 * Class LocatorAwareInterface
 * @package yTheme\Resolvers
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
