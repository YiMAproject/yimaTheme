<?php
namespace yimaTheme\View\Helper;

use yimaTheme\Theme\LocatorDefaultInterface;
use yimaTheme\Theme\ThemeDefaultInterface;
use Zend\View\Helper\AbstractHelper;

/**
 * Class ThemeHelper
 * @package yimaTheme\View\Helper
 */
class ThemeHelper extends AbstractHelper
{
    /**
     * @var LocatorDefaultInterface
     */
    protected $themeLocator;

    /**
     * @var ThemeDefaultInterface
     */
    protected $themeObject;


    /**
     * Construct
     *
     * @param ThemeDefaultInterface $themeObject
     */
    public function __construct(ThemeDefaultInterface $themeObject)
    {
        $this->themeObject  = $themeObject;
    }

    /**
     * Class act as functor
     *
     * @return ThemeDefaultInterface
     */
    public function __invoke()
    {
        return $this->themeObject;
    }
}
