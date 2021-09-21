<?php

namespace Ip\PageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class IpPageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('ip_page', $config);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        foreach ($config as $key => $value) {
            $container->setParameter('ip_page.' . $key, $value);
        }

        $this->registerWidget($container);

        $modules = [];

        if (!empty($config['article'])) {
            $this->loadArticle($config['article'], $container, $loader, $modules);
        }

        $container->setParameter('ip_page.modules', $modules);

        $custom = "";
        $container->setParameter('ip_page.custom.root', "");

        if (!empty($config['custom_site'])) {
            $this->loadCustom($config['custom_site'], $container, $loader, $custom);
        }

        $container->setParameter('ip_page.custom', $custom);

        $this->loadFont($config, $container);
    }

    private function loadArticle(array $config, ContainerBuilder $container, $loader, &$modules)
    {
        if ($config['url'] != '') {
            array_push($modules, [
                'name' => 'articles',
                'value' => true
            ]);
        } else {
            array_push($modules, [
                'name' => 'articles',
                'value' => false
            ]);
        }

        foreach ($config as $key => $value) {
            $container->setParameter('ip_page.article.' . $key, $value);
        }
    }

    private function loadCustom(array $config, ContainerBuilder $container, $loader, &$custom)
    {
        if ($config['json_path']) {
            $custom = $config['json_path'] . '/' . $config['filename'];
        }
        $container->setParameter('ip_page.custom.root', $config['json_path']);
    }

    private function loadFont(array $config, ContainerBuilder $container)
    {
        $fonts = [];
        $ignores = [];

        foreach ($config['fonts'] as $key => $font){
            $fonts[] = $font['name'];

            if($font['ignore']){
                $ignores[] = $font['name'];
            }
        }

        $container->setParameter('ip_page.font.fonts', $fonts);
        $container->setParameter('ip_page.font.ignores', $ignores);
    }

    protected function registerWidget(ContainerBuilder $container)
    {
        $templatingEngines = $container->getParameter('templating.engines');
        if (in_array('twig', $templatingEngines)) {
            $formRessource = 'IpPageBundle:Form:ip_page__widget.html.twig';
            $container->setParameter('twig.form.resources', array_merge(
                $container->getParameter('twig.form.resources'),
                array($formRessource)
            ));
        }
    }
}
