<?php
return array(
    'yima-ytheme' => array(
        'theme_name' => 'builder', // used by config name resolver

        'themes_default_path' => __DIR__ .DS. '..' .DS. 'themes',

        'theme_resolver_adapter' => array(
            // resolver instance of AbstractClass => priority
            'yTheme\Resolvers\Theme\Config' => -1000,
            'yTheme\Resolvers\Theme\Sentenced' => -10000, //always return default theme
        ),

        'layout_resolver_adapter' => array(
            'yTheme\Resolvers\Layout\Error' => -10000, // inject exception layouts on 404,504,exception
        ),

        # default layouts, can ovveride by theme specific conf.
        'layout_notfound'  => 'notfound',
        'layout_exception' => 'error',
        'layout_forbidden' => 'forbidden',

        // tanzimat e makhsoos be har template dar injaa gharaar migirad
        'themes' => array(
            'builder' => array(
                # u can change this theme to another folder.(realpath returned automatically)
                'dir_path' => __DIR__ .DS. '..' .DS. 'themes' .DS. 'builder',

                /* also can change within theme.config.php file
                'layout_notfound'  => '404',
                'layout_exception' => 'error',
                'layout_forbidden' => 'forbidden',
                */
            ),
        ),
    ),

	'service_manager' => array(
		'invokables' => array(
            # resolver theme name by config
            'yTheme\Resolvers\Theme\Config' => 'yTheme\Resolvers\Theme\Config',
		),
	),
);