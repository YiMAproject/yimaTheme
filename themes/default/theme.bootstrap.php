<?php
use Zend\Mvc\MvcEvent;

/**
 * @var $this \yimaTheme\Theme\Theme
 */

$this->isFinal = false;

/** @var $sm \Zend\ServiceManager\ServiceManager */
$sm = $this->getServiceLocator();

// -- Register some autoload ------------------------------------------------
\Zend\Loader\AutoloaderFactory::factory(array());

// -- Attach an event to event system ---------------------------------------
/** @var $events \Zend\EventManager\SharedEventManager */
$events = $sm->get('sharedEventManager');
$events->attach(
    'Zend\Mvc\Controller\AbstractController',
    MvcEvent::EVENT_DISPATCH,
    function($e) {
        // do something
    },
    -1000
);

// .... more
