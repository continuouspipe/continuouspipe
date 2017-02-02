<?php

namespace spec\AppBundle\Model\Definition\Dumper;

use AppBundle\Model\Definition\Dumper\MarkdownReferenceDumper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MarkdownReferenceDumperSpec extends ObjectBehavior
{
    function it_does_not_generate_documentation_when_info_and_example_attributes_are_empty(
        ConfigurationInterface $configuration
    ) {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('tasks');

        $configuration->getConfigTreeBuilder()->willReturn($treeBuilder);

        $this->dump($configuration)->shouldReturn('');
    }

    function it_generates_documentation_from_info_and_example_attributes(ConfigurationInterface $configuration)
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('tasks');
        $root
            ->info('# Tasks')
            ->example(
                'tasks:' . "\n" .
                '    foo: bar'
            )
            ->children()
                ->scalarNode('foo')
                    ->info(
                        '## Foo' . "\n" .
                        'Foo is useful for many things.'
                    )
                    ->example([
                        'foo: bar',
                        'foo: bar/baz',
                    ])
                ->end()
            ->end();

        $configuration->getConfigTreeBuilder()->willReturn($treeBuilder);

        $documentation = <<<MD
# Tasks

Example:

    - tasks:
        foo: bar

## Foo
Foo is useful for many things.

Examples:

    - foo: bar
    - foo: bar/baz
MD;

        $this->dump($configuration)->shouldReturn($documentation);
    }
}
