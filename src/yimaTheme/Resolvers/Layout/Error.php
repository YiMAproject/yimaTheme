<?php
namespace yimaTheme\Resolvers\Layout;

use yimaTheme\Resolvers\ResolverInterface;
use yimaTheme\Resolvers\MvcResolverAwareInterface;
use yimaTheme\Resolvers\ConfigResolverAwareInterface;

use Zend\View\Model\ModelInterface as ViewModel;

use Zend\Mvc\MvcEvent;

/**
 * Agar dar response status code e khataa baashad bar asaase aan
 * layout e namaaiesh raa dar soorat e vojood avaz mikonad
 *
 */
class Error implements
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

        $model = $e->getResult();
        if (!$model instanceof ViewModel)
            return false;

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $response = $e->getResponse();
        if ($response->isSuccess() || $response->isOk())
            // inject layout only if error happens
            return false;

        // detect theme config key by exception mode
        $confKey  = ($response->isServerError()) ? 'layout_exception'
            :( ($response->isNotFound()) ? 'layout_notfound'
                :( ($response->isForbidden() || 401 == $response->getStatusCode()) ? 'layout_forbidden'
                    : 'layout_exception')  )
        ;

        // get config
        $config = $this->config;
        if (is_array($config) && isset($config['theme_locator']))
            $config = $config['theme_locator'];
        else
            $config = array();

        $template = array_key_exists($confKey, $config)
            ? $config[$confKey]
            : false;

        /* Move this out of here
         * if (! empty($template))
        {
            $sm = $e->getApplication()->getServiceManager();
            $viewResolver   = $sm->get('ViewResolver');

            if ($viewResolver->resolve($template)) {
                // it's a spec/404 or spec/error or any other thing that generated with previous listeners
                $intemplate = $model->getTemplate();
                if ($viewResolver->resolve($intemplate) === false ){
                    // if we cant find inside page for error such as spec/404,
                    // then we only use layout template for rendering error
                    $model = $e->getResult();
                    $model->setTemplate($template);
                    $model->setTerminal(true);

                    $template = false;
                }
            }
        }*/

        return (! empty($template)) ? $template : false;
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