<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use JMS\Serializer\Annotation as JMS;

class UserBillingProfile
{
    /**
     * @JMS\Type("uuid")
     *
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @JMS\Type("ContinuousPipe\Security\User\User")
     *
     * @var User
     */
    private $user;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("array<ContinuousPipe\Security\Team\Team>")
     *
     * @var Collection
     */
    private $teams;

    public function __construct(UuidInterface $uuid, User $user, string $name)
    {
        $this->uuid = $uuid;
        $this->user = $user;
        $this->name = $name;
        $this->teams = new ArrayCollection();
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Team[]|Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }
}
