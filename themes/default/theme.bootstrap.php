<?php
/**
 * @var $this \yimaTheme\Theme\Theme
 */

use Zend\Stdlib\ArrayUtils;
use Zend\Mvc\MvcEvent;

/**
 * Theme Resolver Run This Bootstrap And
 * Fall into Next Theme With Resolver Till Get
 * Into Final Theme
 *
 * By Default is True
 */
$this->isFinal = true;
$this->setTemplate('default-alternate');

// inject variable to view template layout
$this->setVariable('ip', $_SERVER['REMOTE_ADDR']);

// ---- Register Assets File Into AssetManager Service --------------------------------------------------------------------------------------------\
/*
 * These Config must merged to application config at last
 * : see below
 */
$overrideConfigs = array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__.DS.'www',
            ),
        ),
    ),
);

// ---- Merge new config to application merged config ---------------------------------------------------------------------------------------------\
$mergedConf = $this->getServiceLocator()->get('Config');
$config     = ArrayUtils::merge($mergedConf, $overrideConfigs);

$this->getServiceLocator()
    ->setAllowOverride(true)
    ->setService('config', $config)
    ->setAllowOverride(false);




// -- Register some autoload ------------------------------------------------
\Zend\Loader\AutoloaderFactory::factory(array());

// -- Attach an event to event system ---------------------------------------
/** @var $events \Zend\EventManager\SharedEventManager */
$events = $this->getServiceLocator()->get('sharedEventManager');
$events->attach(
    'Zend\Mvc\Controller\AbstractController',
    MvcEvent::EVENT_DISPATCH,
    function($e) {
        // do something
    },
    -1000
);

// .... more
