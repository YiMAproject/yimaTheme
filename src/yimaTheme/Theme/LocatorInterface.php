<?php
namespace yimaTheme\Theme;

use yimaTheme\Theme\Theme as ThemeObject;
use Zend\Mvc\MvcEvent;

interface LocatorInterface
{
    /**
     * Find Matched Theme and return object
     *
     * @return ThemeObject
     */
    public function getTheme();

    /**
     * Get layout name according to MvcEvent on EVENT_DISPATCH
     *
     * @param MvcEvent $e
     *
     * @return mixed
     */
    public function getMvcLayout(MvcEvent $e);
}