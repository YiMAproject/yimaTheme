<?php
namespace yimaTheme\View\Helper;

use yimaTheme\Theme\ThemeDefaultInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\PhpRenderer;

class ThemeHelper extends AbstractHelper
    implements ServiceLocatorAwareInterface
{
    protected $sl;

    function __invoke()
    {
        $theme = $this->getCurrent();
        if (!$theme instanceof ThemeDefaultInterface)
            $theme = $this->getRoot();

        return $theme;
    }

    /**
     * Get Root View Model
     *
     * @return ModelInterface
     */
    function getRoot()
    {
        $sm    = $this->sl->getServiceLocator();
        $event = $sm->get('Application')
            ->getMvcEvent();

        return $event->getViewModel();
    }

    /**
     * Proxy Call to View Model Helper
     *
     * @param $method
     * @param $args
     *
     * @return mixed
     */
    function __call($method, $args)
    {
        return call_user_func_array(
            array($this->attainViewModelHelper(), $method)
            , $args
        );
    }

    /**
     * Attain to View Model Helper
     *
     * ! to get root and current view model
     *
     * @return PhpRenderer
     */
    protected function attainViewModelHelper()
    {
        /** @var PhpRenderer $viewModelHelper */
        $viewModelHelper = $this->getView()
            ->plugin('view_model');

        return $viewModelHelper;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sl = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->sl;
    }
}
