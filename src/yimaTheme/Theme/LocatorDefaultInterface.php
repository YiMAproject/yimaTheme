<?php
namespace yimaTheme\Theme;

use Zend\Mvc\MvcEvent;

/**
 * Interface LocatorDefaultInterface
 *
 * @package yimaTheme\Theme
 */
interface LocatorDefaultInterface extends LocatorInterface
{
    /**
     * Get layout name according to MvcEvent on EVENT_DISPATCH
     *
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function getMvcLayout(MvcEvent $e);
}
