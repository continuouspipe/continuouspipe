<?php

namespace ContinuousPipe\River\Flex;

use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\Flex\ConfigurationGeneration\GeneratedConfiguration;
use ContinuousPipe\Flex\ConfigurationGeneration\GenerationException;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

interface FlowConfigurationGenerator
{
    /**
     * @param RelativeFileSystem $fileSystem
     * @param FlatFlow $flow
     *
     * @throws GenerationException
     *
     * @return GeneratedConfiguration
     */
    public function generate(RelativeFileSystem $fileSystem, FlatFlow $flow) : GeneratedConfiguration;
}
