<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Entity;

use ContinuousPipe\River\CodeRepository;
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
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $codeRepositoryIdentifier;

    /**
     * @ORM\Column(type="object")
     *
     * @var CodeRepository
     */
    public $codeRepository;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $userUsername;
}
