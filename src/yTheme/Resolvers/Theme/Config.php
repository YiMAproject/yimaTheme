<?php
namespace yTheme\Resolvers\Theme;

use yTheme\Resolvers\InterfaceClass;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Config implements
    InterfaceClass,
    ServiceLocatorAwareInterface
{
    protected $name;

    protected $servicemanager;

    public function getName()
    {
        if (! $this->name /* we don`t want change theme name from first*/) {
            $sl = $this->getServiceLocator();

            $config = $sl->get('config');
            if (isset($config['yima-ytheme']) && is_array($config['yima-ytheme'])) {

                $this->name = (isset($config['yima-ytheme']['theme_name'])) ? $config['yima-ytheme']['theme_name'] : false;
            }
        }

        return $this->name;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->servicemanager = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->servicemanager;
    }
}