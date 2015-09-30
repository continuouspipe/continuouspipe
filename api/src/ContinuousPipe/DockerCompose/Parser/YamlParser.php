<?php

namespace ContinuousPipe\DockerCompose\Parser;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlParser
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }

    /**
     * @param string $contents
     *
     * @throws ParseException
     *
     * @return array
     */
    public function parse($contents)
    {
        $parsed = $this->parser->parse($contents);

        if (!is_array($parsed)) {
            throw new ParseException('Unable to parse YAML contents');
        }

        return $parsed;
    }
}
