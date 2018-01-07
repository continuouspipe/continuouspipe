<?php

namespace ContinuousPipe\Pipe\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="idx_deployment_view_by_uuid", columns={"deployment_uuid"}),
 * })
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
