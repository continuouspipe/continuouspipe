<?php

namespace ContinuousPipe\River\Pipeline\Generation;

use ContinuousPipe\River\Filter\ContextFactory;
use ContinuousPipe\River\Pipeline\PipelineTideGenerator;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Tide;

class PreventDuplicatedTideGeneration implements PipelineTideGenerator
{
    private $decoratedGenerator;
    private $contextFactory;
    private $tideStartVoter;

    public function __construct(PipelineTideGenerator $decoratedGenerator, ContextFactory $contextFactory, Tide\StartVoter\TideStartVoter $tideStartVoter)
    {
        $this->decoratedGenerator = $decoratedGenerator;
        $this->contextFactory = $contextFactory;
        $this->tideStartVoter = $tideStartVoter;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(TideGenerationRequest $request): array
    {
        $tides = $this->decoratedGenerator->generate($request);

        if ($request->getGenerationTrigger()->getUser()) {
            return $tides;
        }

        return array_values(array_filter($tides, function (Tide $tide) {
            return $this->tideShouldBeGenerated($tide);
        }));
    }

    /**
     * Returns true if the tide should be generated.
     *
     * @param Tide $tide
     *
     * @return bool
     */
    private function tideShouldBeGenerated(Tide $tide) : bool
    {
        return $this->tideStartVoter->vote(
            $tide,
            $this->contextFactory->create($tide)
        );
    }
}
