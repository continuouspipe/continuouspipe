<?php

namespace ContinuousPipe\River\Flex\CodeArchiveEnhancement;

use ContinuousPipe\Archive\Archive;
use ContinuousPipe\Archive\FileSystemArchive;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\Flex\ConfigurationGenerator;
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
     * @var ConfigurationGenerator
     */
    private $configurationGenerator;

    public function __construct(
        CodeArchiveStreamer $decoratedStreamer,
        FlatFlowRepository $flowRepository,
        ConfigurationGenerator $configurationGenerator
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

        $archive = FileSystemArchive::fromStream($stream, FileSystemArchive::TAR_GZ);
        $generatedConfiguration = $this->configurationGenerator->generate($archive->getFilesystem(), $flow);

        foreach ($generatedConfiguration->getGeneratedFiles() as $generatedFile) {
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
