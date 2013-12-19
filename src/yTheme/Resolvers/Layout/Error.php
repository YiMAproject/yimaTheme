<?php
namespace yTheme\Resolvers\Layout;

use yTheme\Resolvers\InterfaceClass;
use yTheme\Resolvers\LocatorAwareInterface;
use yTheme\Resolvers\EventAwareInterface;
use yTheme\Theme\LocatorInterface;

use Zend\View\Model\ModelInterface as ViewModel;

use Zend\Mvc\MvcEvent;

/**
 * Agar dar response status code e khataa baashad bar asaase aan
 * layout e namaaiesh raa dar soorat e vojood avaz mikonad
 *
 */
class Error implements
    InterfaceClass,
    LocatorAwareInterface,
    EventAwareInterface
{
    protected $name;

    protected $locator;

    protected $event;

    public function getName()
    {
        $e = $this->getEvent();

        $model = $e->getResult();
        if (!$model instanceof ViewModel) {
            return;
        }

        $response = $e->getResponse();

        if ( $response->isSuccess() || $response->isOk() ) {
            return;
        }

        $template = '';
        // detect theme config key by exception mode
        $confKey  = ($response->isServerError()) ? 'layout_exception'
            :( ($response->isNotFound()) ? 'layout_notfound'
                :( ($response->isForbidden() || 401 == $response->getStatusCode()) ? 'layout_forbidden'
                    : 'layout_exception')  )

        ;

        // get theme specific layout name by exception mode
        $config = $this->getLocator()->getOptions()->getProps();

        // get layout specific name from theme config
        if ($config) {
            $template = array_key_exists($confKey,$config)
                ? $config[$confKey]
                : '';
        }

        if (isset($config[$confKey]) && empty($template)) {
            // get default specific layouts
            $template = $config[$confKey];
        }

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

    public function setLocator(LocatorInterface $manager)
    {
        $this->locator = $manager;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    public function setEvent(MvcEvent $event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }
}