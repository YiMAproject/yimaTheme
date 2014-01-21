<?php
namespace yimaTheme\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ThemeHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();

        $themeLocator   = $serviceManager->get('yimaTheme\Theme\Locator');
        $themeHelper    = new ThemeHelper($themeLocator);

        return $themeHelper;
    }
}
