Enterprise Template Module
=========

*this module is part of Yima Application Framework*

What this module provides?
------------

#### Multiple Templates
 we can have multiple themes stored in themes folder.

#### Put Themes Anywhere
 here we can have multiple templates that each one stored to different or same folder as default themes folder.
 this can help that each modules required yTheme can have own template inside module package. (useful for modules like administration backend)

#### Resolvers
 we have resolvers for themes and layout.
 resolvers are a class that extended from *resolver with priority number registered through config files.

 think we need user of application can choose own template,
 ok we write a themeReolver that looking in cookies and brought a template name, done.

 we use this theory for layouts too.

#### Impact On The Whole Application
 templates on-demand can impact whole application system.(when template resolved)

 what this mean ?
 templates have configuration file, in this config we can do such things.
 exp.
 + register some autoload config
 + each template can register own view helper
 + we can override or add any config to merged config before application bootstrap
 + with above we can have, controllers, route, navigation, or change render engine and more
 + you can have your own themeObject and your own way.

#### Design With Widgets Support In Mind
 we can inject some widget(widgets are viewModel, string or toString object) for each layout

Instruction
-----------

*step into codes, explore default theme syndicate and see comments*

Installation 
-----------

Composer installation:

require ```rayamedia/yima-ytheme``` in your ```composer.json```

Or clone to modules folder

Enable module with name ```yTheme```

Note: see yTheme\themes\syndicate\www\README.md