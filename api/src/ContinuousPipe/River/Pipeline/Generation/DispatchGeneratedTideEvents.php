<?php

namespace ContinuousPipe\River\Pipeline\Generation;

use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use SimpleBus\Message\Bus\MessageBus;

class DispatchGeneratedTideEvents implements PipelineTideGenerator
{
    /**
     * @var PipelineTideGenerator
     */
    private $decoratedGenerator;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param PipelineTideGenerator $decoratedGenerator
     * @param MessageBus            $eventBus
     */
    public function __construct(PipelineTideGenerator $decoratedGenerator, MessageBus $eventBus)
    {
        $this->decoratedGenerator = $decoratedGenerator;
        $this->eventBus = $eventBus;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(TideGenerationRequest $request): array
    {
        $tides = $this->decoratedGenerator->generate($request);

        foreach ($tides as $tide) {
            foreach ($tide->popNewEvents() as $event) {
                $this->eventBus->handle($event);
            }
        }

        return $tides;
    }
}
