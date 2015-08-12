<?php

namespace ContinuousPipe\River\Flow\Request;

use ContinuousPipe\River\Flow\Task;
use JMS\Serializer\Annotation as JMS;

class FlowCreationRequest
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $repository;

    /**
     * @JMS\Type("array<ContinuousPipe\River\Flow\Task>")
     *
     * @var Task[]
     */
    private $tasks;

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return \ContinuousPipe\River\Flow\Task[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
