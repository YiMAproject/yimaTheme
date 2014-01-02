<?php
/*
 * Necessary Scripts for Head Section of layouts
 * included in syndicate layout files
 */
?>
<?php
echo $this->headTitle('Yima '. $this->translate('Application Framework'))
    ->setSeparator(' - ')
    ->setAutoEscape(false)
?>

<?php
echo $this->headMeta()
    //<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    ->appendName('viewport', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no')
    // <meta http-equiv="X-UA-Compatible" content="IE=edge">
    ->appendHttpEquiv('X-UA-Compatible', 'IE=edge')
?>

<?php
$this->headLink(
    array(
        'rel'  => 'shortcut icon',
        'type' => 'image/vnd.microsoft.icon',
        // use Yima favicon, stored on root. staticUri by default use basePath
        'href' => $this->staticUri() . '/favicon.ico'
    )
);

// Add new path to staticUri {
if (! $this->staticUri('self')->hasPath('staticuri.syndicate')) {
    // set new uri path for syndicate theme
    $this->staticUri('self')
        ->setPath('staticuri.syndicate', $this->basePath().'/syndicate');
}
// ... }

echo $this->headLink()
    ->prependStylesheet(
    // basepath will replaced with $path in $path/syndicate pattern defined in theme config  #  ^
        $this->staticUri('staticuri.syndicate').                                                 # / \
        '/css/style.css'                                                                         # ||
    )                                                                                            # ||
    ->prependStylesheet($this->staticUri('staticuri.syndicate.cdn').'/css/bootstrap.min.css')    # ||
    // continue using syndicate.cdn pattern with no param(s)
    ->appendStylesheet($this->staticUri('staticuri.syndicate') . '/css/font-awesome.css')

#            ->appendStylesheet('http://fonts.googleapis.com/css?family=Open+Sans:300,400,700')

    //->appendStylesheet($this->staticUri() . '/css/ie.css', 'screen', 'lte IE 8')
;
?>

<script src="<?php echo $this->staticUri('staticuri.syndicate.cdn'); ?>/js/modernizr.js" type="text/javascript"></script>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
<?php // staticUri continue using previous url setting for syndicate ?>
<script src="<?php echo $this->staticUri(); ?>/js/html5shiv.js"></script>
<![endif]-->