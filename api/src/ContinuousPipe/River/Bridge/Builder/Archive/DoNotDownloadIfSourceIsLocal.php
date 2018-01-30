<?php

namespace ContinuousPipe\River\Bridge\Builder\Archive;

use ContinuousPipe\Builder\Archive\ArchiveDownloader;
use ContinuousPipe\Builder\Archive\ArchiveException;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use function GuzzleHttp\Psr7\copy_to_stream;
use function GuzzleHttp\Psr7\stream_for;
use Ramsey\Uuid\Uuid;

class DoNotDownloadIfSourceIsLocal implements ArchiveDownloader
{
    private $decoratedDownloader;
    private $flatFlowRepository;
    private $codeArchiveStreamer;
    private $riverUrl;

    public function __construct(ArchiveDownloader $decoratedDownloader, FlatFlowRepository $flatFlowRepository, CodeArchiveStreamer $codeArchiveStreamer, string $riverUrl)
    {
        $this->decoratedDownloader = $decoratedDownloader;
        $this->riverUrl = $riverUrl;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->codeArchiveStreamer = $codeArchiveStreamer;
    }

    /**
     * {@inheritdoc}
     */
    public function download(ArchiveSource $archive, string $to)
    {
        if (strpos($archive->getUrl(), 'https://'.$this->riverUrl) !== 0
            || !preg_match('#/flows/([a-z0-9-]+)/source-code/archive/([^/]+)$#', $archive->getUrl(), $matches)) {

            $this->decoratedDownloader->download($archive, $to);

            return;
        }

        $flow = $this->flatFlowRepository->find(Uuid::fromString($matches[1]));

        try {
            copy_to_stream(
                $this->codeArchiveStreamer->streamCodeArchive($flow->getUuid(), new CodeReference($flow->getRepository(), $matches[2])),
                stream_for(fopen($to, 'w'))
            );
        } catch (\RuntimeException $e) {
            throw new ArchiveException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
