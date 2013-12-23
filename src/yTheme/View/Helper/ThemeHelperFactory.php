<?php
namespace yTheme\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ThemeHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();

        $themeLocator   = $serviceManager->get('yTheme\Theme\Locator');
        $themeHelper    = new ThemeHelper($themeLocator);

        return $themeHelper;
    }
}
