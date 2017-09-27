<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Context;
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
    public function createArchive(BuildStepConfiguration $buildStepConfiguration) : Archive
    {
        try {
            $archive = $this->archivePacker->createFromArchiveRequest(
                $buildStepConfiguration->getContext(),
                $buildStepConfiguration->getArchive()
            );
        } catch (ClientException $e) {
            throw new ArchiveCreationException($e->getMessage(), $e->getCode(), $e);
        }

        return $archive;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool
    {
        return $buildStepConfiguration->getArchive() !== null;
    }
}
