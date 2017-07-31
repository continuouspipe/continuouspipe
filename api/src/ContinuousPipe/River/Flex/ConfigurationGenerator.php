<?php

namespace ContinuousPipe\River\Flex;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\Flex\ConfigurationGeneration\GeneratedConfiguration;
use ContinuousPipe\Flex\ConfigurationGeneration\GenerationException;
use ContinuousPipe\Flex\GenerateFilesAsPerGeneratorMapping;
use ContinuousPipe\Flex\Symfony\ContinuousPipeGenerator;
use ContinuousPipe\Flex\Symfony\DockerComposeGenerator;
use ContinuousPipe\Flex\Symfony\DockerGenerator;
use ContinuousPipe\Flex\Symfony\GenerateFilesWithSymfonyContext;
use ContinuousPipe\River\Flex\ConfigurationGeneration\EncryptedVariableDefinitionGenerator;
use ContinuousPipe\River\Flex\ConfigurationGeneration\GeneratorForFlow;
use ContinuousPipe\River\Flex\FileSystem\FlySystemAdapter;
use ContinuousPipe\River\Flow\EncryptedVariable\EncryptedVariableVault;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

class ConfigurationGenerator
{
    /**
     * @var GeneratorForFlow
     */
    private $generatorForFlow;

    /**
     * @param GeneratorForFlow $generatorForFlow
     */
    public function __construct(GeneratorForFlow $generatorForFlow)
    {
        $this->generatorForFlow = $generatorForFlow;
    }

    /**
     * @param RelativeFileSystem $fileSystem
     * @param FlatFlow $flow
     *
     * @throws GenerationException
     *
     * @return GeneratedConfiguration
     */
    public function generate(RelativeFileSystem $fileSystem, FlatFlow $flow) : GeneratedConfiguration
    {
        if (null === ($configuration = $flow->getFlexConfiguration())) {
            throw new \InvalidArgumentException('The flow must be flexed in order to generate the configuration');
        }

        return $this->generatorForFlow->get($flow)->generate(new FlySystemAdapter($fileSystem));
    }
}
