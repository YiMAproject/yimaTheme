<?php
namespace yimaTheme\Resolvers;

use Zend\Mvc\MvcEvent;

interface MvcResolverAwareInterface
{
    public function setMvcEvent(MvcEvent $e);
}
