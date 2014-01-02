<?php
/*
 * Syndicate Theme Scripts
 * These scripts makes layouts to syndicate
 *
 * included in bottom of default.phtml, home.phtml
 */
$this->headScript()
    // enable responsive features in IE8
    ->prependFile(
        $this->staticUri('staticuri.syndicate.cdn').'/js/respond.min.js',
        'text/javascript',
        array('conditional' => 'lte IE 8',)
    )
    ->prependFile($this->staticUri().'/js/bootstrap.min.js')
    ->prependFile($this->staticUri().'/js/jquery-1.10.2.min.js')

    ->appendFile($this->staticUri().'/js/retina.js')
    ->appendFile($this->staticUri().'/js/jquery.easing.js')
    ->appendFile($this->staticUri().'/js/jquery.fitvids.min.js')
    ->appendFile($this->staticUri().'/js/jquery.nicescroll.min.js')
    ->appendFile($this->staticUri().'/js/jquery.touchwipe.min.js')
    ->appendFile($this->staticUri().'/js/skrollr.js')
    ->appendFile($this->staticUri().'/js/skrollr.js')
    ->appendFile($this->staticUri().'/js/skrollr.ie.min.js', 'text/javascript', array('conditional' => 'lt IE 9'))
    // tour
    ->appendFile($this->staticUri().'/js/guideline.main.js')
    // work / blog
    ->appendFile($this->staticUri().'/js/toucheffects.js')
    ->appendFile($this->staticUri().'/js/modals.js')
    ->appendFile($this->staticUri().'/js/custom.js')

    ->captureStart();
?>
    //<script>
    // menu close on select<script>
    if ($('.navbar-toggle:visible').length) {
        $('.navbar a').click(function () { $(".navbar-responsive-collapse").collapse("hide") });
    }
    //</script>
<?php
$this->headScript()->captureEnd();