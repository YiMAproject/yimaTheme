<?php
return array(

    /*
     * > Themes config name key
     *   exp. merged with [yima-theme] [
     *   'themes' => array(
            'builder' => array(
             ...
         ]
     */

    'widgets' => array(
        /*
        'layout_name' => array(
            'area_name' => array(
                'widget/as/service',
                'string.....'
            ),
        ),
        */
        'home' => array( // or any Itterate object with area key and widget value
            'bottomSection' => array(                                             #  |
                // Widgets can be string, viewModel, or __toString               <- /
                'syndicate.widget.services',
            ),
        ),
    ),

    /*
     * > Setter Config keys
     *
     * exp. 'setter_option_method' mapped to ThemeObject::setterOptionMethod('value')
     */

    #'setter_option_method' => 'value',
    'path_stack_resolver_suffix' => 'phtml',

    /*
     * > Stored as a config options and can get later
     *
     * exp. 'background' => 'wood',
     */


    /*
     * > Theme Locator config
     */

    'theme_locator' => array(
        'layout_notfound'  => 'error',
        'layout_exception' => 'error',
        'layout_forbidden' => 'error',

        'mvclayout_resolver_adapter' => array(
            // set layout by Route Name, if **layout_route_name**.phtml exists change layout into
            'themeSyndicate\Resolvers\Layout\RouteName' => 1000,
        ),
        // ...
    ),

    /*
     * > Application System Configuration Override
     * _
     */

    'application' => array (
        // <<<<<<<<<<<------ Zend\Autoload Config ------>>>>>>>>>>>>>
        'autoloader' => array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'themeSyndicate' => __DIR__ .DS. 'src' .DS,
                ),
            ),
        ),

        // <<<<<<<<<<<------ Modules, Merged Config ----->>>>>>>>>>>>>
        'view_manager' => array (
            // change default layout
            'layout'       => 'default',
        ),

        'static_uri_helper' => array (
            'staticuri.syndicate.cdn' => '//cdn.raya-media.com',
        ),

        // <<<<<<<<<<<----- Services Configuration ------>>>>>>>>>>>>>
        /*
        'registered_service' => array(
            // ... keys supported by
            // Zend\ServiceManager\Config::configureServiceManager()
        ),
        */
        'ViewHelperManager' => array(
            // configure viewHelper, register some view helpers
            'factories' => array (
                'staticUri'   => 'themeSyndicate\View\Helper\StaticUriHelperFactory',
            ),
        ),

        'service_manager' => array(
            'factories' => array(
                'syndicate.widget.services' => function ($sm) {
                        $event = $sm->get('Application')->getMvcEvent();
                        $routeMatch = $event ->getRouteMatch();
                        if ($routeMatch instanceof \Zend\Mvc\Router\Http\RouteMatch) {
                            $routeMatch = $routeMatch->getMatchedRouteName();
                        }

                        if (! $routeMatch == 'home') {
                            return null;
                        }

                        return include_once 'inc_widget_services.php';
                },
            ),
        ),
    ),
);
