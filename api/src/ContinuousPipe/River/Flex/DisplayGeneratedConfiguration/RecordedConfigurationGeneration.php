<?php

namespace ContinuousPipe\River\Flex\DisplayGeneratedConfiguration;

use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\Flex\ConfigurationGeneration\GeneratedConfiguration;
use ContinuousPipe\River\Flex\FlowConfigurationGenerator;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class RecordedConfigurationGeneration implements FlowConfigurationGenerator
{
    /**
     * @var GeneratedConfiguration|null
     */
    private $lastGeneratedConfiguration;

    /**
     * @var FlowConfigurationGenerator
     */
    private $decoratedGenerator;

    /**
     * @param FlowConfigurationGenerator $decoratedGenerator
     */
    public function __construct(FlowConfigurationGenerator $decoratedGenerator)
    {
        $this->decoratedGenerator = $decoratedGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(RelativeFileSystem $fileSystem, FlatFlow $flow): GeneratedConfiguration
    {
        return $this->lastGeneratedConfiguration = $this->decoratedGenerator->generate($fileSystem, $flow);
    }

    /**
     * @return GeneratedConfiguration|null
     */
    public function getLastGeneratedConfiguration()
    {
        return $this->lastGeneratedConfiguration;
    }
}
