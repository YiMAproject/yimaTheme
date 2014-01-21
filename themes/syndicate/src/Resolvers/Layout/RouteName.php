<?php
namespace themeSyndicate\Resolvers\Layout;

use yimaTheme\Resolvers\ResolverInterface;
use yimaTheme\Resolvers\MvcResolverAwareInterface;
use yimaTheme\Resolvers\ConfigResolverAwareInterface;

use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\MvcEvent;

/**
 * Class RouteName
 *
 * @package themeSyndicate\Resolvers\Layout
 */
class RouteName implements
    ResolverInterface,
    MvcResolverAwareInterface,
    ConfigResolverAwareInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    public function getName()
    {
        $e = $this->mvcEvent;
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch instanceof RouteMatch) {
            // nothing to do !!
            return false;
        }

        $routeName = $routeMatch->getMatchedRouteName();

        $sm = $e->getApplication()->getServiceManager();
        $viewResolver   = $sm->get('ViewResolver');
        $template = ($viewResolver->resolve($routeName)) ? $routeName : false;

        return $template;
    }

    public function setMvcEvent(MvcEvent $e)
    {
        $this->mvcEvent = $e;
    }

    /**
     * Set yimaTheme merged config
     *
     * @param Array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}