<?php

namespace Ip\PageBundle\Form\Type;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpPageType extends AbstractType
{
    protected $prefix;
    protected $pBiblio;
    protected $bgcolor;
    protected $color;
    protected $modules;
    protected $fonts;
    protected $ignores;
    protected $folderWebfont;
    protected $fontsUrl;
    protected $customSections;
    protected $customRoot;

    public function __construct($rootDir, $prefix, $pBiblio, $bgcolor, $color, $modules, $fonts, $ignores, $folderWebfont, $customSections, $customRoot)
    {
        $this->prefix = $prefix;
        $this->pBiblio = $pBiblio;
        $this->bgcolor = $bgcolor;
        $this->color = $color;
        $this->modules = $modules;
        $this->fonts = $fonts;
        $this->ignores = $ignores;
        $this->folderWebfont = $folderWebfont;
        $this->customSections = $customSections;
        $this->fontsUrl = $this->findFonts($rootDir);
        $this->customRoot = $customRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'prefix' => $this->prefix,
            'pBiblio' => $this->pBiblio,
            'bgcolor' => $this->bgcolor,
            'color' => $this->color,
            'modules' => json_encode($this->modules),
            'fonts' => json_encode($this->fonts),
            'ignores' => json_encode($this->ignores),
            'customSections' => $this->customSections,
            'fontsUrl' => $this->fontsUrl,
            'customRoot' => $this->customRoot
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'prefix' => null
        ));
    }

    public function getParent()
    {
        return TextareaType::class;
    }

    private function findFonts($rootDir)
    {
        $fontsUrl = [];

        $finder = new Finder();
        $finder->files()->in($rootDir . '/../web' . $this->folderWebfont)->notName('*.min.*');

        foreach ($finder as $file){
            if($file->getExtension() == 'css'){
                $fontsUrl[] = $this->folderWebfont . '/' . $file->getRelativePath() . '/' . $file->getBasename();
            }
        }

        return $fontsUrl;
    }
}