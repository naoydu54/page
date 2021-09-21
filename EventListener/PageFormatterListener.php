<?php

namespace Ip\PageBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Ip\PageBundle\Mapping\Annotation\ToTemplate;
use PHPHtmlParser\Dom;
use Symfony\Component\DependencyInjection\ContainerInterface;
use voku\helper\AntiXSS;

class PageFormatterListener
{
    private $urlArticle;
    private $classToRemove;
    private $container;

    public function __construct($urlArticle, ContainerInterface $container)
    {
        $this->urlArticle = $urlArticle;
        $this->container = $container;
        $this->classToRemove = [
            'summernote',
            'ippage-right-click-edit',
            'image-edit',
            'sortable-section',
            'section-hover',
            'section',
            'ippage-changesize',
            'image-section',
            'row-section',
            'ipvresize',
            'v-resize',
            'btn-dl',
            'edit-container',
            'espace',
            'resize-content',
            'module-fullhtml',
            'ippage-right-click-icon',

              
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->bindHtml($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->bindHtml($args);
    }

    private function bindHtml(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $reflectionClass = new \ReflectionClass(get_class($entity));
        // Prepare doctrine annotation reader
        $reader = new AnnotationReader();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($annotation = $reader->getPropertyAnnotation($reflectionProperty, ToTemplate::class)) {
                $method = $reflectionClass->getMethod('set'.ucfirst($reflectionProperty->getName()));
                $result = $reflectionClass->getMethod('get'.ucfirst($annotation->getFrom()))->invokeArgs($entity, []);
                $method->invokeArgs($entity, [$result]);
                $html = $this->compile($reflectionClass->getMethod('get'.ucfirst($reflectionProperty->getName()))->invokeArgs($entity, []));
                if ($html == '') {
                    $method->invokeArgs($entity, ['']);
                } else {
                    $method->invokeArgs($entity, [$html]);
                }
            }
        }
    }

    private function compile($html)
    {
        $html = $this->removeStyleSection($html);
        //$html = $this->jquerySortableShit($html);
        $html = $this->summernote($html);
        $html = $this->replace_img_src($html);
        $html = $this->removeUselessButton($html);
        if ($this->urlArticle != '') {
            $html = $this->replaceArticle($html);
        }
        $html = $this->cleanEmbed($html);
        $html = $this->parallax($html);
        $html = $this->linkFile($html);
        $html = $this->createContainer($html);
        $html = $this->includeModule($html);
        $html = $this->fullHtml($html);
        $html = $this->removeClass($html);

        return $html;
    }

    private function fullHtml($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);

        /** @var Dom\HtmlNode[] $divs */
        $divs = $dom->find('div.module-fullhtml');

        $fullHtml = null;

        if (count($divs) > 0) {
            foreach ($divs as $div) {

                $div->removeChild($div->firstChild()->id());

                /** @var Dom\HtmlNode $child */
                foreach ($div->getChildren() as $child) {
                    if (strpos($child->getTag()->getAttribute('class')['value'], 'fullhtml-container') !== false) {
                        $child->removeAttribute('class');
                    }
                }

            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function createContainer($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);

        /** @var Dom\HtmlNode[] $divs */
        $divs = $dom->find('div[data-container]');

        if (count($divs) > 0) {
            foreach ($divs as $div) {
                $dataContainer = $div->getTag()->getAttribute('data-container');

                if (!is_null($dataContainer)) {
                    $div->setAttribute('class', $dataContainer['value']);
                    $div->removeAttribute('data-container');
                }

                $dataColor = $div->getTag()->getAttribute('data-color');

                if (!is_null($dataColor)) {
                    $div->removeAttribute('data-color');
                }
            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function includeModule($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);

        /** @var Dom\HtmlNode[] $divs */
        $divs = $dom->find('.module');

        if (count($divs) > 0) {
            foreach ($divs as $div) {
                $dataController = $div->getTag()->getAttribute('data-controller');
                if ($dataController) {
                    $textContent = "{{ render(controller('".$dataController['value']."')) }}";

                    foreach ($div->getChildren() as $child) {
                        $div->removeChild($child->id());

                        $textNode = new Dom\TextNode($textContent);
                        $div->addChild($textNode);
                    }

                    $div->removeAttribute('data-controller');
                    $div->removeAttribute('class');
                }
            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function linkFile($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);

        /** @var Dom\HtmlNode[] $linkFiles */
        $linkFiles = $dom->find('.btn-dl');

        if (count($linkFiles) > 0) {
            foreach ($linkFiles as $linkFile) {
                $dataHref = $linkFile->getTag()->getAttribute('data-href');

                if (!is_null($dataHref)) {
                    $linkFile->setAttribute('href', $dataHref['value']);
                    $linkFile->removeAttribute('data-href');
                }
            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    /*private function createContainer($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        /** @var Dom\HtmlNode[] $sections *
        $sections = $dom->find('.parent');
    }*/

    private function removeStyleSection($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        /** @var Dom\HtmlNode[] $sections */
        $sections = $dom->find('.section');

        /** @var Dom\HtmlNode $root */
        $root = $dom->root;

        if (count($sections) > 0) {
            foreach ($sections as $section) {
                $section->getTag()->removeAllAttributes();
                $inner = $section->innerhtml;
                $tnode = new Dom\TextNode($inner);
                $section->delete();
                $root->addChild($tnode);
            }
            $dom->root = $root;

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function jquerySortableShit($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        /** @var Dom\HtmlNode[] $sections */
        $sections = $dom->find('.section-hover');

        /** @var Dom\HtmlNode $root */
        $root = $dom->root;

        if (count($sections) > 0) {
            foreach ($sections as $section) {
                $section->getTag()->removeAllAttributes();
                $inner = $section->innerhtml;
                $tnode = new Dom\TextNode($inner);
                $section->delete();
                $root->addChild($tnode);
            }
            $dom->root = $root;

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function summernote($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        /** @var Dom\HtmlNode[] $summernotes */
        $summernotes = $dom->find('.summernote');
        if (count($summernotes) > 0) {
            /** @var Dom\HtmlNode $root */
            $root = $dom->root;

            foreach ($summernotes as $summernote) {
                $parent = $summernote->getParent();
                $inner = $summernote->innerhtml;
                $tnode = new Dom\TextNode($inner);
                $summernote->delete();
                $parent->addChild($tnode);
            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function replace_img_src($original_img_tag)
    {
        $dom = new Dom();
        $dom->load($original_img_tag);
        /** @var Dom\HtmlNode[] $images */
        $images = $dom->find('img');

        if (count($images) > 0) {
            foreach ($images as $image) {
                $url = $image->getTag()->getAttribute('src');
                $url = $url['value'];
                $image->setAttribute('src', '{{\''.$url.'\' | image([1000,1000], "inset") }}');
            }

            return $dom->__toString();
        }

        return $original_img_tag;
    }

    private function removeUselessButton($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        /** @var Dom\HtmlNode[] $buttons */
        $buttons = $dom->find('.btn-add-col');
        if (count($buttons) > 0) {
            foreach ($buttons as $button) {
                $button->delete();
            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function replaceArticle($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        /** @var Dom\HtmlNode[] $articles */
        $articles = $dom->find('.article');
        if (count($articles) > 0) {
            foreach ($articles as $article) {
                /** @var Dom\HtmlNode $parent */
                $parent = $article->getParent();
                $article->delete();
                $inner = new Dom\TextNode('{{ render(url(\''.$this->urlArticle.'\')) }}');
                $parent->addChild($inner);
            }

            return $dom->__toString();
        }

        return $originalHtml;
    }

    private function cleanEmbed($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);

        /** @var Dom\HtmlNode[] $elements */
        $elements = $dom->find('.embed-responsive');

        foreach ($elements as $element) {
            /** @var Dom\HtmlNode[] $children */
            $children = $element->getChildren();
            foreach ($children as $child) {
                /** @var Dom\Tag $tag */
                $tag = $child->tag;
                if ($tag->name() != "iframe") {
                    $child->outerHtml();
                }
            }
        }

        return $dom->__toString();
    }

    private function parallax($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);

        /** @var Dom\HtmlNode[] $elements */
        $elements = $dom->find('.parallax-window');

        foreach ($elements as $element) {
            $element->setAttribute('style', '');
        }

        return $dom->__toString();
    }

    private function removeClass($originalHtml)
    {
        $dom = new Dom();
        $dom->load($originalHtml);
        foreach ($this->classToRemove as $ctr) {
            /** @var Dom\HtmlNode[] $elements */
            $elements = $dom->find('.'.$ctr);
            foreach ($elements as $element) {
                $element->setAttribute('class', str_replace($ctr, '', $element->getAttribute('class')));
            }
        }

        return $dom->__toString();
    }
}