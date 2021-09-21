<?php

namespace Ip\PageBundle\DependencyInjection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ip_page');
        $rootNode
            ->children()
            ->scalarNode('assets_path')->defaultValue('/assets/ippage')->end()
            ->scalarNode('webfont')->defaultValue('/webfonts')->end()
            ->scalarNode('color')->defaultValue('#ffffff')->end()
            ->scalarNode('bgcolor')->defaultValue('#000000')->end()
            ->booleanNode('include_assets')->defaultTrue()->end()
            ->booleanNode('include_jQuery')->defaultFalse()->end()
            ->booleanNode('include_bootstrap')->defaultFalse()->end()
            ->scalarNode('model_manager_name')->defaultNull()->end()
            ->end();
        $this->addArticleSection($rootNode);
        $this->addCustomSection($rootNode);
        $this->addFontSection($rootNode);

        return $treeBuilder;
    }

    private function addArticleSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
            ->arrayNode('article')
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->children()
            ->scalarNode('url')->defaultValue('')->end()
            ->end()
            ->end()
            ->end();
    }

    public function addCustomSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
            ->arrayNode('custom_site')
            ->children()
            ->scalarNode('json_path')->end()
            ->scalarNode('filename')->defaultValue('sections.json')
            ->end()
            ->end();
    }

    private function addFontSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
            ->arrayNode('fonts')
            ->prototype('array')
            ->children()
            ->scalarNode('name')->end()
            ->booleanNode('ignore')->defaultTrue()->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }
}
