<?php

namespace ContinuousPipe\River\Flow;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * This class is intended to be used for functional testing only.
 *
 * It describes a sample configuration schema.
 */
class TestConfiguration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->booleanNode('foo')
                    ->info($this->getFooNodeDocumentation())
                    ->example($this->getFooNodeExamples())
                    ->defaultTrue()
                ->end()
                ->scalarNode('bar')
                    ->info($this->getBarNodeDocumentation())
                    ->example($this->getBarNodeExamples())
                    ->defaultValue('baz')
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function getFooNodeDocumentation()
    {
        return <<<MD
# Foo

Foo is a boolean config option. Defaults to true.
MD;

    }

    private function getFooNodeExamples()
    {
        return [
            'foo: false',
            'foo: true',
        ];
    }

    private function getBarNodeDocumentation()
    {
        return <<<MD
# Bar

Bar is a scalar config option. Defaults to "baz".
MD;
    }

    private function getBarNodeExamples()
    {
        return [
            'bar: qwerty',
        ];
    }
}