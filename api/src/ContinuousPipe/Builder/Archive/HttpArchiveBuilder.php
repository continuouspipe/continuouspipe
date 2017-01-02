<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;
use GuzzleHttp\Exception\ClientException;
use LogStream\Logger;

class HttpArchiveBuilder implements ArchiveBuilder
{
    /**
     * @var ArchivePacker
     */
    private $archivePacker;

    /**
     * @param ArchivePacker $archivePacker
     */
    public function __construct(ArchivePacker $archivePacker)
    {
        $this->archivePacker = $archivePacker;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, Logger $logger)
    {
        try {
            $archive = $this->archivePacker->createFromArchiveRequest($buildRequest->getContext(), $buildRequest->getArchive());
        } catch (ClientException $e) {
            throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
        }

        return $archive;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildRequest $request)
    {
        return $request->getArchive() !== null;
    }
}
