<?php
namespace yTheme\Resolvers;

use Zend\Mvc\MvcEvent;

interface MvcResolverAwareInterface
{
    public function setMvcEvent(MvcEvent $e);
}
