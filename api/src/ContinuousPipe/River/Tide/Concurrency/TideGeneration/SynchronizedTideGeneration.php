<?php

namespace ContinuousPipe\River\Tide\Concurrency\TideGeneration;

use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Tide;

class SynchronizedTideGeneration implements PipelineTideGenerator
{
    /**
     * @var PipelineTideGenerator
     */
    private $decoratedGenerator;

    /**
     * @var Tide\Concurrency\Lock\Locker
     */
    private $locker;

    /**
     * @param PipelineTideGenerator        $decoratedGenerator
     * @param Tide\Concurrency\Lock\Locker $locker
     */
    public function __construct(PipelineTideGenerator $decoratedGenerator, Tide\Concurrency\Lock\Locker $locker)
    {
        $this->decoratedGenerator = $decoratedGenerator;
        $this->locker = $locker;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(TideGenerationRequest $request): array
    {
        return $this->locker->lock(
            $this->getLockerReference($request),
            function () use ($request) {
                return $this->decoratedGenerator->generate($request);
            }
        );
    }

    private function getLockerReference(TideGenerationRequest $request) : string
    {
        return sprintf(
            'flow-%s',
            (string) $request->getFlow()->getUuid()
        );
    }
}
