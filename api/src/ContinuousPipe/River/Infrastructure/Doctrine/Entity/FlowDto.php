<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity;

use ContinuousPipe\River\FlowContext;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
}
