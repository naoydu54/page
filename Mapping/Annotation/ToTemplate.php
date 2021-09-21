<?php

namespace Ip\PageBundle\Mapping\Annotation;

/**
 * @Annotation
 */
class ToTemplate{
    private $from;
    private $out = "twig";

    public function __construct(array $options)
    {
        if (isset($options['value'])) {
            $options['propertyName'] = $options['value'];
            unset($options['value']);
        }

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }
            $this->$key = $value;
        }
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getOut()
    {
        return $this->out;
    }
}