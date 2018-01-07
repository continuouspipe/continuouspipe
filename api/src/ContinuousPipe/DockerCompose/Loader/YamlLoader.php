<?php

namespace ContinuousPipe\DockerCompose\Loader;

use ContinuousPipe\DockerCompose\Parser\YamlParser;
use ContinuousPipe\DockerCompose\Transformer\EnvironmentTransformer;

class YamlLoader
{
    /**
     * @var EnvironmentTransformer
     */
    private $environmentTransformer;
    /**
     * @var
     */
    private $yamlParser;

    /**
     * @param EnvironmentTransformer $environmentTransformer
     * @param YamlParser             $yamlParser
     */
    public function __construct(EnvironmentTransformer $environmentTransformer, YamlParser $yamlParser)
    {
        $this->environmentTransformer = $environmentTransformer;
        $this->yamlParser = $yamlParser;
    }

    /**
     * @param string $environmentIdentifier
     * @param string $string
     *
     * @return \ContinuousPipe\Model\Environment
     */
    public function load($environmentIdentifier, $string)
    {
        $parsed = $this->yamlParser->parse($string);

        return $this->environmentTransformer->load($environmentIdentifier, $parsed);
    }
}
