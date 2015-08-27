<?php

namespace ContinuousPipe\Pipe\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DeploymentViewDto
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    public $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    public $deploymentUuid;

    /**
     * @ORM\Column(type="text", nullable=false)
     *
     * @var string
     */
    public $serializedDeploymentView;
}
