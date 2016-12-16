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
     * @param UuidInterface $generationUuid
     * @param FlatFlow      $flow
     * @param CodeReference $codeReference
     */
    public function __construct(UuidInterface $generationUuid, FlatFlow $flow, CodeReference $codeReference)
    {
        $this->flow = $flow;
        $this->codeReference = $codeReference;
        $this->generationUuid = $generationUuid;
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
}
