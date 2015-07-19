<?php

namespace ContinuousPipe\Authenticator\Infrastructure\Doctrine\Entity;

use ContinuousPipe\User\DockerRegistryCredentials;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class UserDockerRegistryCredentialsDto
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
     * @ORM\Column(type="string")
     *
     * @var string
     */
    public $userUsername;

    /**
     * @ORM\Embedded(class="ContinuousPipe\User\DockerRegistryCredentials", columnPrefix=false)
     *
     * @var DockerRegistryCredentials
     */
    public $credentials;
}
