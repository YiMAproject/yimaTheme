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
     * Class act as functor
     *
     * @return ThemeDefaultInterface
     */
    public function __invoke()
    {
        $viewModel = $this->getView()
            ->plugin('view_model')
            ->getCurrent();

        if (!$viewModel instanceof ThemeDefaultInterface) {
            // this is not Theme Object
            // return root theme
        }

        return $viewModel;
    }
}
