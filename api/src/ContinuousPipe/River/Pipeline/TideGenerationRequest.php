<?php

namespace ContinuousPipe\River\Pipeline;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Ramsey\Uuid\UuidInterface;

final class TideGenerationRequest
{
    /**
     * @var FlatFlow
     */
    private $flow;
    /**
     * @var CodeReference
     */
    private $codeReference;
    /**
     * @var UuidInterface
     */
    private $generationUuid;
    /**
     * @var TideGenerationTrigger
     */
    private $generationTrigger;
    /**
     * @var UuidInterface
     */
    private $targetTideUuid;

    /**
     * @param UuidInterface $generationUuid
     * @param FlatFlow $flow
     * @param CodeReference $codeReference
     * @param TideGenerationTrigger $generationTrigger
     * @param UuidInterface $targetTideUuid
     */
    public function __construct(
        UuidInterface $generationUuid,
        FlatFlow $flow,
        CodeReference $codeReference,
        TideGenerationTrigger $generationTrigger,
        UuidInterface $targetTideUuid = null
    ) {
        $this->flow = $flow;
        $this->codeReference = $codeReference;
        $this->generationUuid = $generationUuid;
        $this->generationTrigger = $generationTrigger;
        $this->targetTideUuid = $targetTideUuid;
    }

    /**
     * @return FlatFlow
     */
    public function getFlow(): FlatFlow
    {
        return $this->flow;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference(): CodeReference
    {
        return $this->codeReference;
    }

    /**
     * @return UuidInterface
     */
    public function getGenerationUuid(): UuidInterface
    {
        return $this->generationUuid;
    }

    /**
     * @return TideGenerationTrigger
     */
    public function getGenerationTrigger(): TideGenerationTrigger
    {
        return $this->generationTrigger;
    }

    /**
     * @return UuidInterface|null
     */
    public function getTargetTideUuid()
    {
        return $this->targetTideUuid;
    }
}
