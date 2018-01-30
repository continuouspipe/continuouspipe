<?php


namespace ContinuousPipe\River\Flex;

use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use ContinuousPipe\Flex\ConfigurationGeneration\GeneratedConfiguration;
use ContinuousPipe\Flex\ConfigurationGeneration\GenerationException;
use ContinuousPipe\River\Flex\ConfigurationGeneration\GeneratorForFlow;
use ContinuousPipe\River\Flex\FileSystem\FlySystemAdapter;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class GenerateConfigurationFromFlexGenerator implements FlowConfigurationGenerator
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
     * {@inheritdoc}
     */
    public function generate(RelativeFileSystem $fileSystem, FlatFlow $flow) : GeneratedConfiguration
    {
        if (null === ($configuration = $flow->getFlexConfiguration())) {
            throw new \InvalidArgumentException('The flow must be flexed in order to generate the configuration');
        }

        try {
            return $this->generatorForFlow->get($flow)->generate(new FlySystemAdapter($fileSystem));
        } catch (\InvalidArgumentException $e) {
            throw new GenerationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
