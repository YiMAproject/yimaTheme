<?php
namespace themeSyndicate\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StaticUriHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceManager = $serviceLocator->getServiceLocator();

        $conf     = $serviceManager->get('Config');
        $conf     = ( isset($conf['static_uri_helper']) && is_array($conf['static_uri_helper']) )
                  ? $conf['static_uri_helper']
                  : array();

        $stServer = new StaticUri($conf);

        return $stServer;
    }
}
