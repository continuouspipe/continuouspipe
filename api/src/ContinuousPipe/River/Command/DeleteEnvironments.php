<?php

namespace ContinuousPipe\River\Command;

use ContinuousPipe\River\CodeReference;
use Ramsey\Uuid\Uuid;
use JMS\Serializer\Annotation as JMS;

class DeleteEnvironments
{
    /**
     * @JMS\Type("Ramsey\Uuid\Uuid")
     *
     * @var Uuid
     */
    private $flowUuid;

    /**
     * @JMS\Type("ContinuousPipe\River\CodeReference")
     *
     * @var CodeReference
     */
    private $codeReference;

    /**
     * @param Uuid          $flowUuid
     * @param CodeReference $codeReference
     */
    public function __construct(Uuid $flowUuid, CodeReference $codeReference)
    {
        $this->flowUuid = $flowUuid;
        $this->codeReference = $codeReference;
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid(): Uuid
    {
        return $this->flowUuid;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference(): CodeReference
    {
        return $this->codeReference;
    }
}
