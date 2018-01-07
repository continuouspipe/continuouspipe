<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity;

use ContinuousPipe\River\FlowContext;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_flow_dto_by_team", columns={"team_slug"}),
 *     @ORM\Index(name="idx_flow_dto_repository_type_and_identifier", columns={"repository_type", "repository_identifier"})
 * })
 */
class FlowDto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var string
     */
    public $uuid;

    /**
     * @ORM\Column(type="b64Object")
     *
     * @var FlowContext
     */
    public $context;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $userUsername;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    public $teamSlug;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    public $repositoryType;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    public $repositoryIdentifier;
}
