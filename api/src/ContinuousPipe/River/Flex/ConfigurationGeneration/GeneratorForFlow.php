<?php

namespace ContinuousPipe\River\Flex\ConfigurationGeneration;

use ContinuousPipe\Flex\ConfigurationGeneration\ConfigurationGenerator;
use ContinuousPipe\Flex\ConfigurationGeneration\GenerateConfigurationWithDefaultContext;
use ContinuousPipe\Flex\ConfigurationGeneration\Sequentially\SequentiallyGenerateFiles;
use ContinuousPipe\Flex\ConfigurationGeneration\Symfony\Context\WithSymfonyContext;
use ContinuousPipe\Flex\ConfigurationGeneration\Symfony\ContinuousPipeGenerator;
use ContinuousPipe\Flex\ConfigurationGeneration\Symfony\DockerComposeGenerator;
use ContinuousPipe\Flex\ConfigurationGeneration\Symfony\DockerGenerator;
use ContinuousPipe\River\Flex\FlexConfiguration;
use ContinuousPipe\River\Flow\EncryptedVariable\EncryptedVariableVault;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

final class GeneratorForFlow
{
    /**
     * @var EncryptedVariableVault
     */
    private $vault;

    /**
     * @var array
     */
    private $defaultVariables;

    /**
     * @param EncryptedVariableVault $vault
     * @param array $defaultVariables
     */
    public function __construct(EncryptedVariableVault $vault, array $defaultVariables)
    {
        $this->vault = $vault;
        $this->defaultVariables = $defaultVariables;
    }

    /**
     * @param FlatFlow $flow
     *
     * @throws \InvalidArgumentException
     *
     * @return ConfigurationGenerator
     */
    public function get(FlatFlow $flow) : ConfigurationGenerator
    {
        if (null === ($configuration = $flow->getFlexConfiguration())) {
            $configuration = new FlexConfiguration(
                'unknown'
            );
        }

        return new GenerateConfigurationWithDefaultContext(
            new WithSymfonyContext(
                new SequentiallyGenerateFiles([
                    new DockerGenerator(),
                    new DockerComposeGenerator(),
                    new ContinuousPipeGenerator(
                        new EncryptedVariableDefinitionGenerator($this->vault, $flow->getUuid())
                    ),
                ])
            ),
            [
                'variables' => $this->defaultVariables,
                'image_name' => 'quay.io/continuouspipe-flex/flow-'.$flow->getUuid()->toString(),
                'endpoint_host_suffix' => '-'.$configuration->getSmallIdentifier().'-flex.continuouspipe.net',
                'cluster' => 'flex',
                'continuous_pipe_defaults' => [
                    'environment' => [
                        'name' => '\''.$flow->getUuid()->toString().'-\' ~ code_reference.branch',
                    ]
                ]
            ]
        );
    }
}
