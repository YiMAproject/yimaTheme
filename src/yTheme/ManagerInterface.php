<?php
namespace yTheme;

use Zend\Mvc\MvcEvent;

interface ManagerInterface
{
    /**
     * Init Theme Manager To Work
     *
     * @return mixed
     */
    public function init(MvcEvent $e);
}