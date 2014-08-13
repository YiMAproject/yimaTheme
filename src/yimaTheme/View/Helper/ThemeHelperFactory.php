<?php
namespace yimaTheme\View\Helper;

use yimaTheme\Manager;
use yimaTheme\ManagerInterface;
use yimaTheme\Theme\ThemeDefaultInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ThemeHelperFactory
 * @package yimaTheme\View\Helper
 */
class ThemeHelperFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $serviceLocator \Zend\View\HelperPluginManager */
        $sm = $serviceLocator->getServiceLocator();

        /** @var $themeManager Manager */
        $themeManager = $sm->get('yimaTheme.Manager');
        if (!$themeManager instanceof ManagerInterface) {
            throw new \Exception(
                sprintf(
                    'Theme Manager Must Instance Of "ManagerInterface" But "%s" Given.'
                    , is_object($themeManager) ? get_class($themeManager) : gettype($themeManager)
                )
            );
        }

        $themeObject = $themeManager->getThemeObject();
        if (!$themeObject instanceof ThemeDefaultInterface) {
            throw new \Exception('Not Valid ThemeObject Provided By ThemeManager, It Must an Instance Of "ThemeDefaultInterface".');
        }

        return new ThemeHelper($themeObject);
    }
}
