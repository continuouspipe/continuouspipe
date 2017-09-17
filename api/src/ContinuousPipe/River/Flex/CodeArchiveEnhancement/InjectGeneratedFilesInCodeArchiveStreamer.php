<?php

namespace ContinuousPipe\River\Flex\CodeArchiveEnhancement;

use ContinuousPipe\Archive\Archive;
use ContinuousPipe\Archive\ArchiveException;
use ContinuousPipe\Archive\FileSystemArchive;
use ContinuousPipe\Flex\ConfigurationGeneration\GeneratedFile;
use ContinuousPipe\Flex\ConfigurationGeneration\GenerationException;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\Flex\FlowConfigurationGenerator;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\UuidInterface;

class InjectGeneratedFilesInCodeArchiveStreamer implements CodeArchiveStreamer
{
    /**
     * @var CodeArchiveStreamer
     */
    private $decoratedStreamer;

    /**
     * @var FlatFlowRepository
     */
    private $flowRepository;

    /**
     * @var FlowConfigurationGenerator
     */
    private $configurationGenerator;

    public function __construct(
        CodeArchiveStreamer $decoratedStreamer,
        FlatFlowRepository $flowRepository,
        FlowConfigurationGenerator $configurationGenerator
    ) {
        $this->decoratedStreamer = $decoratedStreamer;
        $this->flowRepository = $flowRepository;
        $this->configurationGenerator = $configurationGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function streamCodeArchive(UuidInterface $flowUuid, CodeReference $codeReference): StreamInterface
    {
        $flow = $this->flowRepository->find($flowUuid);
        $stream = $this->decoratedStreamer->streamCodeArchive($flowUuid, $codeReference);

        // If the flow is not flexified, we just return the stream
        if (!$flow->isFlex()) {
            return $stream;
        }

        try {
            $archive = FileSystemArchive::fromStream($stream, FileSystemArchive::TAR_GZ);
        } catch (ArchiveException $e) {
            throw new CodeRepositoryException('Archive from code repository is not a valid TAR-GZ file.', $e->getCode(), $e);
        }

        try {
            $generatedConfiguration = $this->configurationGenerator->generate($archive->getFilesystem(), $flow);
        } catch (GenerationException $e) {
            throw new CodeRepositoryException(sprintf('Could not generate configuration files: %s', $e->getMessage()), $e->getCode(), $e);
        }

        /** @var GeneratedFile[] $successfullyGeneratedFiles */
        $successfullyGeneratedFiles = array_filter($generatedConfiguration->getGeneratedFiles(), function (GeneratedFile $file) {
            return !$file->hasFailed();
        });

        foreach ($successfullyGeneratedFiles as $generatedFile) {
            $archive->writeFile($generatedFile->getPath(), $generatedFile->getContents());
        }

        return $archive->read(Archive::TAR_GZ);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CodeReference $codeReference): bool
    {
        return $this->decoratedStreamer->supports($codeReference);
    }
}
