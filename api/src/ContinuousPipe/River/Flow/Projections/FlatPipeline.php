<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\Pipeline\Pipeline;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class FlatPipeline
{
    /**
     * @JMS\Type("uuid")
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @JMS\Type("ContinuousPipe\River\Flow\Projections\FlatFlow")
     */
    private $flow;

    public function __construct(UuidInterface $uuid, string $name)
    {
        $this->uuid = $uuid;
        $this->name = $name;
    }

    public static function fromPipeline(Pipeline $pipeline) : FlatPipeline
    {
        return new self(
            $pipeline->getUuid(),
            $pipeline->getName()
        );
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * This method is used by Doctrine.
     *
     * @param FlatFlow $flow
     */
    public function setFlow(FlatFlow $flow)
    {
        $this->flow = $flow;
    }
}
