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
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $hasTrial;

    public function __construct(UuidInterface $uuid, User $user, string $name, \DateTimeInterface $creationDate, bool $hasTrial)
    {
        $this->uuid = $uuid;
        $this->user = $user;
        $this->name = $name;
        $this->creationDate = $creationDate;
        $this->hasTrial = $hasTrial;
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
}
