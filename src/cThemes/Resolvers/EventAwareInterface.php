<?php
namespace cThemes\Resolvers;

use Zend\Mvc\MvcEvent;

/**
 * Resolver classes that implement this can have MVCEvent injected into
 *
 * Class EventAwareInterface
 * @package cThemes\Resolvers
 */
interface EventAwareInterface
{
    /**
     *
     * @param MvcEvent $event
     */
    public function setEvent(MvcEvent $event);

    /**
     *
     * @return MvcEvent
     */
    public function getEvent();
}
