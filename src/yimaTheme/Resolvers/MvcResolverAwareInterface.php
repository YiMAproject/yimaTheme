<?php
namespace yimaTheme\Resolvers;

use Zend\Mvc\MvcEvent;

interface MvcResolverAwareInterface
{
    /**
     * Inject Mvc Event Object
     *
     * @param MvcEvent $e MvcEvent Object
     *
     * @return $this
     */
    public function setMvcEvent(MvcEvent $e);
}
