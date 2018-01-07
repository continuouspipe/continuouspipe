<?php

namespace ContinuousPipe\River\Pipeline\Command;

use ContinuousPipe\River\Command\FlowCommand;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use Ramsey\Uuid\UuidInterface;

final class GenerateTides implements FlowCommand
{
    private $request;

    public function __construct(TideGenerationRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest(): TideGenerationRequest
    {
        return $this->request;
    }

    public function getFlowUuid(): UuidInterface
    {
        return $this->request->getFlow()->getUuid();
    }
}
