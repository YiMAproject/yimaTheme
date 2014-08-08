<?php
namespace yimaTheme;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\View\Resolver as ViewResolver;

/**
 * Class Manager
 *
 * @package yimaTheme
 */
class Manager implements
    ManagerInterface,
    ServiceManagerAwareInterface,
    EventManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /*
     * @var EventManagerInterface
     */
    protected $events;

    protected $isInitialized;

    /**
     * Init Theme Manager To Work
     *
     * @return $this
     */
    public function init()
    {
        if ($this->isInitialized()) {
            return true;
        }

        // attach default listeners
        /** @var $sharedEvents \Zend\EventManager\SharedEventManager */
        $sm = $this->getServiceManager();
        $defaultListeners = $sm->get('yimaTheme\ThemeManager\ListenerAggregate');

        $sharedEvents = $this->getEventManager()->getSharedManager();
        $sharedEvents->attachAggregate($defaultListeners);

        $this->isInitialized = true;

        return $this;
    }

    /**
     * Determine theme is loaded or not?
     *
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    // -- implementation methods --------------------------------------------------------------------------------------

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Get serviceManager
     *
     * @return ServiceManager
     *
     * @throws \Exception
     */
    public function getServiceManager()
    {
        if (! $this->serviceManager) {
            throw new \Exception('ServiceManager not injected and not exists.');
        }

        return $this->serviceManager;
    }

    /**
     * Inject an EventManager instance
     *
     * @param  EventManagerInterface $eventManager
     * @return void
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->events = $eventManager;
    }

    /**
     * Retrieve the event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->events;
    }
}
