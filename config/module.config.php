<?php
return array(
    'yima-ytheme' => array(
        'theme_locator' => array(
            /*
             * Resolve to the theme and return theme object to locator
             *
             * also can used as once, like:
             * 'resolver_adapter' => 'resolver\service'
             */
            'resolver_adapter_service' => array(
                // resolver => priority
                'yTheme\Resolvers\Theme\Config' => -1000,
                'yTheme\Resolvers\Theme\Sentenced' => -10000, //always return default theme
            ),
            'default_theme_name'  => 'builder', // used by yTheme\Resolvers\Theme\Config
            'themes_default_path' => (defined(APP_DIR_APPLICATION))
                    ? APP_DIR_APPLICATION .DS. 'themes' // used in Yima
                    : 'your_path_to_themes',

            // .............................................................................................

            'mvclayout_resolver_adapter' => array(
                'yTheme\Resolvers\Layout\Error' => -10000, // inject exception layouts on 404,504,exception
            ),
            # default layouts, can override by theme specific conf.
            // used by yTheme\Resolvers\Layout\Error
            'layout_notfound'  => '404',
            'layout_exception' => 'error',
            'layout_forbidden' => 'forbidden',
        ),

        // tanzimat e makhsoos be har template dar injaa gharaar migirad
        # Note: after including current detected theme specific config, -
        # config with theme name merged here ...
        'themes' => array(
            'builder' => array(
                # u can change this theme to another folder.(realpath returned automatically)
                # in this folder folder with builder (name of theme) must found.
                'dir_path' => __DIR__ .DS. '..' .DS. 'themes',
            ),
        ),
    ),

	'service_manager' => array(
        // attention: some services registered via Module::getServiceConfig
		'invokables' => array(
            # resolver theme name by config
            'yTheme\Resolvers\Theme\Config' => 'yTheme\Resolvers\Theme\Config',
		),
	),
);