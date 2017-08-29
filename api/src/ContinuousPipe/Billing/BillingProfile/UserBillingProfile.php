<?php

namespace ContinuousPipe\Billing\BillingProfile;

use ContinuousPipe\Billing\Plan\Plan;
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
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @JMS\Type("boolean")
     *
     * TODO: Replace by a plan!
     *
     * @var bool
     */
    private $hasTrial;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $tidesPerHour;

    /**
     * @JMS\Type("array<ContinuousPipe\Security\User\User>")
     *
     * @var User[]|Collection
     */
    private $admins;

    /**
     * @JMS\Type("array<ContinuousPipe\Security\Team\Team>")
     *
     * @var Team[]|Collection
     */
    private $teams;

    /**
     * @JMS\Type("ContinuousPipe\Billing\Plan\Plan")
     *
     * @var Plan|null
     */
    private $plan;

    public function __construct(UuidInterface $uuid, string $name, \DateTimeInterface $creationDate, $admins = null, bool $hasTrial = false, int $tidesPerHour = 0, Plan $plan = null)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->creationDate = $creationDate;
        $this->admins = !$admins instanceof Collection ? new ArrayCollection($admins) : $admins;
        $this->hasTrial = $hasTrial;
        $this->tidesPerHour = $tidesPerHour;
        $this->plan = $plan;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return User[]|Collection
     */
    public function getAdmins()
    {
        if (null === $this->admins) {
            $this->admins = new ArrayCollection();
        }

        return $this->admins;
    }

    /**
     * @return Team[]|Collection
     */
    public function getTeams()
    {
        if (null === $this->teams) {
            $this->teams = new ArrayCollection();
        }

        return $this->teams;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @return bool
     */
    public function hasTrial(): bool
    {
        return $this->hasTrial ?: false;
    }

    /**
     * @return int
     */
    public function getTidesPerHour(): int
    {
        return $this->tidesPerHour ?: 0;
    }

    /**
     * @return Plan|null
     */
    public function getPlan()
    {
        return $this->plan;
    }

    public function setTidesPerHour(int $tiderPerHour)
    {
        $this->tidesPerHour = $tiderPerHour;
    }

    public function isAdmin(User $user) : bool
    {
        foreach ($this->getAdmins() as $admin) {
            if ($admin->getUsername() == $user->getUsername()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Collection|User[] $admins
     */
    public function setAdmins(Collection $admins)
    {
        $this->admins = $admins;
    }

    /**
     * @param Team[]|Collection $teams
     */
    public function setTeams(Collection $teams)
    {
        $this->teams = $teams;
    }

    public function withPlan(Plan $plan = null)
    {
        $this->plan = $plan;

        return $this;
    }
}
