<?php

namespace ContinuousPipe\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('continuous_pipe_security');

        $root
            ->children()
                ->arrayNode('vaults')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('google_kms')
                                ->children()
                                    ->scalarNode('project_id')->isRequired()->end()
                                    ->scalarNode('location')->isRequired()->end()
                                    ->scalarNode('service_account_path')->isRequired()->end()
                                    ->scalarNode('key_ring')->isRequired()->end()
                                    ->scalarNode('key_cache_service')->end()
                                ->end()
                            ->end()
                            ->arrayNode('php_encryption')
                                ->children()
                                    ->scalarNode('key')->isRequired()->end()
                                ->end()
                            ->end()
                            ->scalarNode('cache_service')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
