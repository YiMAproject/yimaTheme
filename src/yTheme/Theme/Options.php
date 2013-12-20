<?php
namespace yTheme\Theme;

use yTheme\Theme\Locator;
use Traversable;
use Zend\Stdlib\ArrayUtils;

class Options extends Locator
{
    protected $options;

    /**
     * use for in class caching
     *
     * @var
     */
    protected $servicesOptions;

    protected $params = array();

    /**
     * Constructor
     *
     * @param  array|Traversable|null $options
     */
    public function __construct(Locator $locator)
    {
        foreach ($locator as $key=>$val) {
            // cloning base locator variables into the class.
            //  class e options daghighan variable haaye locator raa baa meghdaar e aan daashte baashad
            // chon class az locator extend shode va variable haa mojood ast digar __set call nemishavad
            $this->$key = $val;
        }

        $options = $this->getOptionsFromFile();
        if (null !== $options) {
            $this->setFromArray($options);
        }
    }

    /**
     * Set one or more configuration properties
     *
     * @param  array|Traversable $options
     */
    public function setFromArray($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new \Exception(sprintf(
                'Parameter provided to %s must be an %s, %s or %s',
                __METHOD__, 'array', 'Traversable', 'Zend\Stdlib\AbstractOptions'
            ));
        }

        foreach ($options as $key => $value) {
            $this->__set($key, $value);
        }

        return $this;
    }



    /**
     * Get a configuration property
     *
     * @see ParameterObject::__get()
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $return = null;
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        if (method_exists($this, $getter)) {
            $return = $this->{$getter}();
        } elseif(array_key_exists($key,$this->options)) {
            $return = $this->options[$key];
        }

        return $return;
    }

    public function __set($name, $value)
    {
        $callSetMethod = 'set'. str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        if (method_exists($this,$callSetMethod)) {
            $this->{$callSetMethod}($value);
        } else {
            $this->options[$name] = $value;
        }
    }

    public function toArray()
    {
        return $this->options;
    }

    public function getProps()
    {
        $themeName = $this->getName();

        return (isset($this->options['yima-ytheme']['themes'][$themeName]))
               ? $this->options['yima-ytheme']['themes'][$themeName]
               : array();
    }

    public function setLayoutResolverAdapter($options)
    {
        if (! is_array($options)) {
            if ($options instanceof \Iterator) {
                $options = ArrayUtils::iteratorToArray($options);
            }

            // is string
            if (is_string($options)) {
                $options = array(
                    "{$options}" => 1
                );
            }
        }

        if (empty($options)) {
            return;
        }

        $this->options['yima-ytheme']['layout_resolver_adapter'] = $options;
    }

    /* Tamaami e option haaii ke baraie yek theme e khaas e konooni estefaade mishavand
     *  Be $this->options['themes'][$themeName] enteghaal miaabad
     *  va tavasote toArray ghaabele dastyaabi ast.
     *  { ...
     * */

    public function setDirPath($options)
    {
        if (empty($options)) {
            return;
        }

        $themeName = $this->getName();

        $this->options['yima-ytheme']['themes'][$themeName]['dir_path'] = $options;
    }

    public function setLayoutNotfound($options)
    {
        if (empty($options))
        {
            return;
        }

        $themeName = $this->getName();

        $this->options['yima-ytheme']['themes'][$themeName]['layout_notfound'] = $options;
    }

    public function setLayoutException($options)
    {
        if (empty($options)) {
            return;
        }

        $themeName = $this->getName();

        $this->options['yima-ytheme']['themes'][$themeName]['layout_exception'] = $options;
    }

    public function setLayoutForbidden($options)
    {
        if (empty($options)) {
            return;
        }

        $themeName = $this->getName();

        $this->options['yima-ytheme']['themes'][$themeName]['layout_forbidden'] = $options;
    }

    public function setWidgets($options)
    {
        if (empty($options)) {
            return;
        }

        $themeName = $this->getName();
        $this->options['yima-ytheme']['themes'][$themeName]['widgets'] = $options;
    }

    /* ... } */

}
