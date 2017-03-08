<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity\View;

use ContinuousPipe\River\Flow\Projections\FlatPipeline;
use ContinuousPipe\River\View\Tide;
use Doctrine\ORM\Mapping as ORM;
use LogStream\Tree\TreeLog;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_tide_dto_by_flow", columns={"flow_uuid"}),
 *     @ORM\Index(name="idx_tide_dto_by_sha1_and_branch", columns={"code_reference_sha1", "code_reference_branch"}),
 *     @ORM\Index(name="idx_tide_dto_by_flow_sha1_and_branch", columns={"flow_uuid", "code_reference_sha1", "code_reference_branch"}),
 *     @ORM\Index(name="idx_tide_dto_by_flow_and_branch", columns={"flow_uuid", "code_reference_branch"}),
 *     @ORM\Index(name="idx_tide_dto_by_flow_branch_and_status", columns={"flow_uuid", "code_reference_branch", "status"}),
 *     @ORM\Index(name="idx_tide_dto_by_flow_and_status", columns={"flow_uuid", "status"}),
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
     * @ORM\ManyToOne(targetEntity="ContinuousPipe\River\Flow\Projections\FlatPipeline", cascade={"persist"})
     * @ORM\JoinColumn(name="pipeline_uuid", referencedColumnName="uuid", nullable=true, onDelete="CASCADE")
     *
     * @var FlatPipeline
     */
    private $pipeline;

    /**
     * Create a DTO from the tide.
     *
     * @param Tide $tide
     * @param FlatPipeline $pipeline
     *
     * @return TideDto
     */
    public static function fromTide(Tide $tide, FlatPipeline $pipeline)
    {
        $dto = new self();
        $dto->uuid = $tide->getUuid();
        $dto->flowUuid = $tide->getFlowUuid();
        $dto->merge($tide, $pipeline);

        return $dto;
    }

    /**
     * @param Tide $tide
     * @param FlatPipeline $pipeline
     */
    public function merge(Tide $tide, FlatPipeline $pipeline)
    {
        $this->tide = $tide;
        $this->pipeline = $pipeline;
    }

    /**
     * @return Tide
     */
    public function toTide() : Tide
    {
        $wrappedTide = $this->getTide();

        $tide = Tide::create(
            $this->uuid,
            $this->flowUuid,
            $wrappedTide->getCodeReference(),
            TreeLog::fromId($wrappedTide->getLogId()),
            $wrappedTide->getTeam(),
            $wrappedTide->getUser(),
            $wrappedTide->getConfiguration() ?: [],
            $wrappedTide->getCreationDate(),
            $wrappedTide->getGenerationUuid(),
            $this->pipeline
        );

        $tide->setStatus($wrappedTide->getStatus());
        $tide->setStartDate($wrappedTide->getStartDate());
        $tide->setFinishDate($wrappedTide->getFinishDate());

        return $tide;
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
