<?php

namespace Jplarar\S3Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jplarar_s3');

        $rootNode
            ->children()
                ->arrayNode('amazon_s3')
                    ->children()
                        ->scalarNode('amazon_s3_key')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('amazon_s3_secret')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('amazon_s3_bucket')
                            ->defaultValue(null)
                        ->end()
                        ->scalarNode('amazon_s3_region')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
