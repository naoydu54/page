<?php

namespace Ip\PageBundle\Twig;

use Symfony\Component\Form\FormView;

class IpPageAppExtension extends \Twig_Extension
{
    private $prefix;

    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('ipPageFormGetParent', [$this, 'ipPageFormGetParent']),
            new \Twig_SimpleFunction('ipPageStyle', [$this, 'ipPageStyle']),
            new \Twig_SimpleFunction('ipPageScript', [$this, 'ipPageScript']),
        ];
    }

    public function ipPageFormGetParent(FormView $formView)
    {
        if (is_null($formView->parent)) {
            return $formView;
        }
        return $this->ipPageFormGetParent($formView->parent);
    }

    public function ipPageStyle()
    {
        return $this->prefix . '/plugins/pagemaker/css/bundle.css';
    }

    public function ipPageScript()
    {
        return $this->prefix . '/plugins/pagemaker/js/bundle.js';
    }

    public function getName()
    {
        return 'ip_page_app_extension';
    }
}
