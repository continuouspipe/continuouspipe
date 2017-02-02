<?php

namespace AppBundle\Model\Definition\Dumper;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Yaml\Inline;

/**
 * Dumps the documentation in Markdown format for the given configuration/node instance.
 *
 * Use the "info" and "example" attribute of configuration nodes to describe the configuration option.
 *
 * Example:
 * $rootNode
 *     ->children()
 *         ->scalarNode('image')
 *             ->info(
 *                  '# Docker image' . "\n" .
 *                  'Syntax: [<namespace]/<image-name>[:tag-name]'
 *              )
 *              ->example(
 *                  'Examples:' . "\n".
 *                  '```' . "\n".
 *                  'image: php' . "\n".
 *                  'image: php:7.1' . "\n".
 *                  'image: phpunit/phpunit' . "\n".
 *                  'image: phpunit/phpunit:5.4.7' . "\n".
 *                  '```'
 *              )
 *             ->defaultValue(25)
 *         ->end()
 *     ->end();
 */
class MarkdownReferenceDumper
{
    private $reference;

    public function dump(ConfigurationInterface $configuration)
    {
        return $this->dumpNode($configuration->getConfigTreeBuilder()->buildTree());
    }

    public function dumpNode(NodeInterface $node)
    {
        $this->reference = '';
        $this->writeNode($node);
        $ref = trim($this->reference);
        $this->reference = null;

        return $ref;
    }

    /**
     * @param NodeInterface $node
     */
    private function writeNode(NodeInterface $node)
    {
        $defaultArray = null;
        $children = null;
        $example = $node->getExample();

        // defaults
        if ($node instanceof ArrayNode) {
            $children = $node->getChildren();

            if ($node instanceof PrototypedArrayNode) {
                $prototype = $node->getPrototype();

                if ($prototype instanceof ArrayNode) {
                    $children = $prototype->getChildren();
                }

                // check for attribute as key
                if ($key = $node->getKeyAttribute()) {
                    $keyNodeClass = 'Symfony\Component\Config\Definition\\'.($prototype instanceof ArrayNode ? 'ArrayNode' : 'ScalarNode');
                    $keyNode = new $keyNodeClass($key, $node);

                    // add children
                    foreach ($children as $childNode) {
                        $keyNode->addChild($childNode);
                    }
                    $children = array($key => $keyNode);
                }
            }
        }

        if ($example && !is_array($example)) {
            $example = [$example];
        }

        if ($info = $node->getInfo()) {
            $this->writeLine('');
            $this->writeLine($info);
            $this->writeLine('');
        }

        if (is_array($example)) {
            $message = count($example) > 1 ? 'Examples' : 'Example';

            $this->writeLine($message.":\n");
            $this->writeArray($example, 1);
        }

        if ($children) {
            foreach ($children as $childNode) {
                $this->writeNode($childNode);
            }
        }
    }

    /**
     * Outputs a single config reference line.
     *
     * @param string $text
     * @param int    $indent
     */
    private function writeLine($text, $indent = 0)
    {
        $indent = strlen($text) + $indent;
        $format = '%'.$indent.'s';

        $this->reference .= sprintf($format, $text)."\n";
    }

    private function writeArray(array $array, $depth)
    {
        $isIndexed = array_values($array) === $array;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $val = '';
            } else {
                $val = $this->indentText($value, $depth * 4);
            }

            if ($isIndexed) {
                $this->writeLine('- '.$val, $depth * 4);
            } else {
                $this->writeLine(sprintf('%-20s %s', $key.':', $val), $depth * 4);
            }

            if (is_array($value)) {
                $this->writeArray($value, $depth + 1);
            }
        }
    }

    private function indentText($text, $indent = 0)
    {
        $lines = explode("\n", $text);
        $formattedLines = array_map(function($line) use($indent) {
            $lineIndent = strlen($line) + $indent;
            return sprintf("%${lineIndent}s", $line);
        }, $lines);

        return trim(implode("\n", $formattedLines));
    }
}
