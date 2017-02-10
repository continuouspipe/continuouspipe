<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Pipeline\View\Storage;

use ContinuousPipe\River\Infrastructure\Firebase\DatabaseFactory;
use ContinuousPipe\River\View\Storage\PipelineViewStorage;
use Firebase\Exception\ApiException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Firebase implementation of the pipeline view storage.
 */
class FirebasePipelineViewStorage implements PipelineViewStorage
{
    /**
     * @var DatabaseFactory
     */
    private $databaseFactory;

    /**
     * @var string
     */
    private $databaseUri;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(DatabaseFactory $databaseFactory, string $databaseUri, LoggerInterface $logger)
    {
        $this->databaseFactory = $databaseFactory;
        $this->databaseUri = $databaseUri;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePipeline(UuidInterface $flowUuid, UuidInterface $pipelineUuid)
    {
        $database = $this->databaseFactory->create($this->databaseUri);

        try {
            // Delete pipeline from by-pipelines view
            $database->getReference(sprintf(
                'flows/%s/tides/by-pipelines/%s',
                (string) $flowUuid,
                (string) $pipelineUuid
            ))->remove();

            // Delete the pipelines' view
            $database->getReference(sprintf(
                'flows/%s/pipelines/%s',
                (string) $flowUuid,
                (string) $pipelineUuid
            ))->remove();
        } catch (ApiException $e) {
            $this->logger->warning('Unable to remove the pipeline view from Firebase', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'flowUuid' => (string) $flowUuid,
                'pipelineUuid' => (string) $pipelineUuid,
            ]);
        }
    }
}
