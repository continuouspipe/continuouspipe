<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity\View;

use ContinuousPipe\River\Infrastructure\Doctrine\Entity\FlowDto;
use ContinuousPipe\River\View\Tide;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TideDto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var string
     */
    private $uuid;

    /**
     * @ORM\Embedded(class="ContinuousPipe\River\View\Tide", columnPrefix=false)
     *
     * @var Tide
     */
    private $tide;

    /**
     * @ORM\ManyToOne(targetEntity="ContinuousPipe\River\Infrastructure\Doctrine\Entity\FlowDto")
     * @ORM\JoinColumn(name="flow_uuid", referencedColumnName="uuid", onDelete="CASCADE")
     *
     * @var FlowDto
     */
    private $flow;

    /**
     * Create a DTO from the tide.
     *
     * @param Tide    $tide
     * @param FlowDto $flowDto
     *
     * @return TideDto
     */
    public static function fromTide(Tide $tide, FlowDto $flowDto)
    {
        $dto = new self();
        $dto->uuid = $tide->getUuid();
        $dto->flow = $flowDto;
        $dto->merge($tide);

        return $dto;
    }

    /**
     * @param Tide $tide
     */
    public function merge(Tide $tide)
    {
        $this->tide = $tide;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return Tide
     */
    public function getTide()
    {
        return $this->tide;
    }

    /**
     * @param Tide $tide
     */
    public function setTide($tide)
    {
        $this->tide = $tide;
    }

    /**
     * @return FlowDto
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @param FlowDto $flow
     */
    public function setFlow($flow)
    {
        $this->flow = $flow;
    }
}
