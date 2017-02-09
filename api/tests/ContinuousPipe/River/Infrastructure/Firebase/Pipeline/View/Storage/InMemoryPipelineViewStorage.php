<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Pipeline\View\Storage;

use ContinuousPipe\River\View\Storage\PipelineViewStorage;
use Ramsey\Uuid\UuidInterface;

/**
 * This in-memory implementation is for testing purposes only.
 */
class InMemoryPipelineViewStorage implements PipelineViewStorage
{
    /**
     * @var array
     */
    private $deletedPipelines = [];

    /**
     * Delete the given pipeline.
     *
     * @param UuidInterface $flowUuid
     * @param UuidInterface $pipelineUuid
     */
    public function deletePipeline(UuidInterface $flowUuid, UuidInterface $pipelineUuid)
    {
        $this->deletedPipelines[] = (string) $pipelineUuid;
    }

    /**
     * Tell whether the given pipeline has been deleted.
     *
     * @param UuidInterface $pipelineUuid
     *
     * @return bool
     */
    public function isPipelineDeleted(UuidInterface $pipelineUuid)
    {
        return in_array((string) $pipelineUuid, $this->deletedPipelines);
    }
}