<?php
return array(
    'yima-theme' => array(
        'theme_locator' => array(
            /*
             * Resolve to the theme and return theme object to locator
             *
             * also can used as once, like:
             * 'resolver_adapter' => 'resolver\service'
             */
            'resolver_adapter_service' => array(
                // resolver => priority
                // Resolvers can be either Service and Class
                'yimaTheme.Resolvers.Theme.Config' => -1000,
                'yimaTheme\Resolvers\Theme\Sentenced' => -10000, //always return default theme
            ),
            # default template name on your themes folder
            'default_theme_name'  => 'default', // used by yimaTheme.Resolvers.Theme.Config
            'themes_default_path' => (defined('APP_DIR_APPLICATION'))
                    ? APP_DIR_APPLICATION .DS. 'themes' // used in Yima
                    : 'your_path_to_themes',

            // .............................................................................................

            'mvclayout_resolver_adapter' => array(
                // inject exception layouts on 404,504,exception
                // we want error layouts above others
                'yimaTheme\Resolvers\Layout\Error' => 10000,
            ),
            # default layouts, can override by theme specific conf.
            // used by yimaTheme\Resolvers\Layout\Error
            'layout_notfound'  => 'default',
            'layout_exception' => 'default',
            'layout_forbidden' => 'default',
        ),

        // tanzimat e makhsoos be har template dar injaa gharaar migirad
        # Note: after including current detected theme specific config, -
        # config with theme name merged here ...
        'themes' => array(
            'default' => array(
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
            'yimaTheme.Resolvers.Theme.Config' => 'yimaTheme\Resolvers\Theme\Config',
		),
	),
);