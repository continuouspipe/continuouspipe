<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity\View;

use ContinuousPipe\River\View\Tide;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_tide_dto_by_flow", columns={"flow_uuid"}),
 *     @ORM\Index(name="idx_tide_dto_by_sha1_and_branch", columns={"code_reference_sha1", "code_reference_branch"}),
 * })
 */
class TideDto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @ORM\Embedded(class="ContinuousPipe\River\View\Tide", columnPrefix=false)
     *
     * @var Tide
     */
    private $tide;

    /**
     * @ORM\Column(name="flow_uuid", type="uuid", nullable=false)
     *
     * @var UuidInterface
     */
    private $flowUuid;

    /**
     * Create a DTO from the tide.
     *
     * @param Tide $tide
     *
     * @return TideDto
     */
    public static function fromTide(Tide $tide)
    {
        $dto = new self();
        $dto->uuid = $tide->getUuid();
        $dto->flowUuid = $tide->getFlowUuid();
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
     * @return UuidInterface
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return Tide
     */
    public function getTide()
    {
        return $this->tide;
    }

    /**
     * @return UuidInterface
     */
    public function getFlowUuid(): UuidInterface
    {
        return $this->flowUuid;
    }
}
