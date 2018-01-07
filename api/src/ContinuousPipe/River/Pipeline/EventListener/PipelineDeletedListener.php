<?php

namespace ContinuousPipe\River\Pipeline\EventListener;

use ContinuousPipe\River\Flow\Event\PipelineDeleted;
use ContinuousPipe\River\View\Storage\PipelineViewStorage;

/**
 * Handle the pipeline deleted event
 *
 * Remove the pipeline from permanent storage.
 */
class PipelineDeletedListener
{
    /**
     * @var PipelineViewStorage
     */
    private $pipelineViewStorage;

    public function __construct(PipelineViewStorage $pipelineViewStorage)
    {
        $this->pipelineViewStorage = $pipelineViewStorage;
    }

    public function notify(PipelineDeleted $event)
    {
        $this->pipelineViewStorage->deletePipeline($event->getFlowUuid(), $event->getPipelineUuid());
    }
}
