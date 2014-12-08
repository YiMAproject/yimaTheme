<?php
namespace yimaTheme\Resolvers\Layout;

use yimaTheme\Resolvers\LocatorResolverAwareInterface;
use yimaTheme\Resolvers\ResolverInterface;
use yimaTheme\Resolvers\MvcResolverAwareInterface;
use yimaTheme\Resolvers\ConfigResolverAwareInterface;

use yimaTheme\Theme\LocatorDefaultInterface;
use Zend\View\Model\ModelInterface as ViewModel;

use Zend\Mvc\MvcEvent;

/**
 * Change Theme Layout If Error Happens
 * - look in "layout_*" on yimaTheme Merged Config in "theme_locator"
 * - look in "layout_*" options for "theme"[detected_theme] ..
 *
 */
class Error implements
    ResolverInterface,
    LocatorResolverAwareInterface,
    MvcResolverAwareInterface,
    ConfigResolverAwareInterface
{
    /**
     * @var LocatorDefaultInterface
     */
    protected $themeLocator;

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
            // No Error Happens We Do Nothing ..
            return false;

        // detect theme config key by exception mode
        $confKey  = ($response->isServerError()) ? 'layout_exception'
            :( ($response->isNotFound()) ? 'layout_notfound'
                :( ($response->isForbidden() || 401 == $response->getStatusCode()) ? 'layout_forbidden'
                    : 'layout_exception')  )
        ;

        // get error template from yimaTheme merged config if exists >>>> {
        $config = $this->config;
        // default error layout
        if (is_array($config) && isset($config['theme_locator']))
            $tconfig = $config['theme_locator'];
        else
            $tconfig = array();

        $template = (string) array_key_exists($confKey, $tconfig)
            ? $tconfig[$confKey]
            : false;

        // specific error layout
        $currTheme = $this->themeLocator->getPreparedThemeObject()->getName();
        if (is_array($config) && isset($config['themes'])
            && isset($config['themes'][$currTheme])
        )
            $tconfig = $config['themes'][$currTheme];
        else
            $tconfig = array();

        $template = array_key_exists($confKey, $tconfig)
            ? $tconfig[$confKey]
            : $template;

        // <<<< }

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

    /**
     * Inject Theme Locator Object
     *
     * @param LocatorDefaultInterface $l
     *
     * @return $this
     */
    public function setThemeLocator(LocatorDefaultInterface $l)
    {
        $this->themeLocator = $l;
    }
}