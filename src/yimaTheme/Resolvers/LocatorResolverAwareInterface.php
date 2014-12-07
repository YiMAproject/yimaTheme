<?php
namespace yimaTheme\Resolvers;
use yimaTheme\Theme\LocatorDefaultInterface;

/**
 * Resolver classes that implement this can have themeLocator injected into
 *
 * Interface MvcResolverAwareInterface
 *
 * @package yimaTheme\Resolvers
 */
interface LocatorResolverAwareInterface
{
    /**
     * Inject Theme Locator Object
     *
     * @param LocatorDefaultInterface $l
     *
     * @return $this
     */
    public function setThemeLocator(LocatorDefaultInterface $l);
}
