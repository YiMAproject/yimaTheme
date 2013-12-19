<?php
return array(
    // specific theme options ... {
    'autoloader' => array(
        'Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                'ThemeStartup' => __DIR__ .DS. 'src' .DS,
            ),
        ),
    ),

    //'dir_path' => __DIR__,
    'layout_notfound'  => '404',
    'layout_exception' => 'fullwidth',
    'layout_forbidden' => 'fullwidth',

    'widgets' => array(
        /*
        'layout_name' => array(
            'area_name' => array(
                'widget/as/service',
                'string.....'
            ),
        ),
        */
        'fullwidth' => array(
            'welcome_area' => array(
                'ThemeStartup\Widget\Tagline', // show messages and navigation according to page
            ),
        ),
    ),

    #'layout_resolver_adapter' => '',

    // ... }


    // use configs override here ... {
    'view_manager' => array (
        // change default layout
        'layout'       => 'fullwidth',
    ),

    'static_uri_helper' => array (
        // a way to easily change assets path or web public folder, used by staticUri helper
        #'namespace\to\theme'   => '/assets/theme/folder',
        'yTheme\theme\builder' => '//raya-media.com/cd/builder',
    ),
    // ... }


    // use services config by name ... {
    'service_manager' => array(
        'factories' => array(
            'ThemeStartup\Widget\Tagline' => function ($sm) {
                $event = $sm->get('Application')->getMvcEvent();
                $routeMatch = $event ->getRouteMatch();
                if ($routeMatch instanceof \Zend\Mvc\Router\Http\RouteMatch) {
                    $routeMatch = $routeMatch->getMatchedRouteName();
                }

                $extra = ($routeMatch == 'home')
                       ? 'Super powerful <span class="colored">&amp; </span>responsive HTML Tempalte with hundreds options.'
                       : null;

                return '<div class="container">
                <div class="row">
                <div class="span12">
                    <div class="welcome">
                        <h3><strong class="colored">'.$routeMatch.': </strong>'.$extra.'</h3>
                    </div>
                </div>
                </div>
            </div>';
            } ,
        ),
    ),

    /*
    'ViewHelperManager' => array(
        // configure viewHelper, register some view helpers
        'invokables' => array (
            'staticUri'   => 'Theme\Startup\View\Helper\staticUri',
        ),
    ),
    */

    // ... }

);
