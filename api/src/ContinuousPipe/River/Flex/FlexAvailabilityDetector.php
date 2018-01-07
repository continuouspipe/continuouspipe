<?php

namespace ContinuousPipe\River\Flex;

use ContinuousPipe\River\CodeRepository\FileSystem\FileNotFound;
use ContinuousPipe\Flex\ConfigurationFileCollectionGenerator;
use ContinuousPipe\Flex\ConfigurationGeneration\GenerationException;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\FileSystemResolver;
use ContinuousPipe\River\Flex\ConfigurationGeneration\GeneratorForFlow;
use ContinuousPipe\River\Flex\FileSystem\FlySystemAdapter;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Psr\Log\LoggerInterface;

class FlexAvailabilityDetector
{
    /**
     * @var FileSystemResolver
     */
    private $fileSystemResolver;

    /**
     * @var GeneratorForFlow
     */
    private $generatorForFlow;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param FileSystemResolver $fileSystemResolver
     * @param GeneratorForFlow $generatorForFlow
     * @param LoggerInterface $logger
     */
    public function __construct(FileSystemResolver $fileSystemResolver, GeneratorForFlow $generatorForFlow, LoggerInterface $logger)
    {
        $this->fileSystemResolver = $fileSystemResolver;
        $this->generatorForFlow = $generatorForFlow;
        $this->logger = $logger;
    }

    /**
     * @param FlatFlow $flow
     *
     * @throws FlexException
     *
     * @return bool
     */
    public function isFlexAvailable(FlatFlow $flow) : bool
    {
        $codeReference = CodeReference::repositoryDefault($flow->getRepository());

        try {
            $fileSystem = $this->fileSystemResolver->getFileSystem($flow, $codeReference);
        } catch (CodeRepositoryException $e) {
            throw new FlexException('Cannot access the code repository', $e->getCode(), $e);
        }

        try {
            $generatedConfiguration = $this->generatorForFlow->get($flow)->generate(new FlySystemAdapter($fileSystem));
        } catch (GenerationException $e) {
            $this->logger->warning('Flex is not available following an exception', [
                'exception' => $e,
                'flow_uuid' => $flow->getUuid()->toString(),
            ]);

            return false;
        }

        foreach ($generatedConfiguration->getGeneratedFiles() as $generatedFile) {
            if ($generatedFile->hasFailed()) {
                $this->logger->warning('Flex is not available following a failed generated file', [
                    'file' => $generatedFile->getPath(),
                    'failure_reason' => $generatedFile->getFailureReason(),
                ]);

                return false;
            }
        }

        return true;
    }
}
