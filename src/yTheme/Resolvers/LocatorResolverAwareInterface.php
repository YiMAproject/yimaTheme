<?php
namespace yTheme\Resolvers;
use yTheme\Theme\LocatorDefaultInterface;

/**
 * Resolver classes that implement this can have themeLocator injected into
 *
 * Interface MvcResolverAwareInterface
 *
 * @package yTheme\Resolvers
 */
interface LocatorResolverAwareInterface
{
    public function setThemeLocator(LocatorDefaultInterface $l);
}
