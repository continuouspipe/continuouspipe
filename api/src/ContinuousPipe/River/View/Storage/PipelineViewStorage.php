<?php

namespace ContinuousPipe\River\View\Storage;

use Ramsey\Uuid\UuidInterface;

interface PipelineViewStorage
{
    /**
     * Delete the given pipeline.
     *
     * @param UuidInterface $flowUuid
     * @param UuidInterface $pipelineUuid
     */
    public function deletePipeline(UuidInterface $flowUuid, UuidInterface $pipelineUuid);
}
