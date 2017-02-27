<?php

namespace ContinuousPipe\DevelopmentEnvironment\Aggregate;

use ContinuousPipe\DevelopmentEnvironment\Aggregate\Events\DevelopmentEnvironmentCreated;
use ContinuousPipe\DevelopmentEnvironment\ReadModel\DevelopmentEnvironment as ReadModelDevelopmentEnvironment;
use ContinuousPipe\River\EventBased\ApplyEventCapability;
use ContinuousPipe\River\EventBased\RaiseEventCapability;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class DevelopmentEnvironment
{
    use ApplyEventCapability, RaiseEventCapability;

    private $uuid;
    private $flowUuid;
    private $username;
    private $name;
    private $modificationDate;

    public static function create(UuidInterface $flowUuid, User $user, string $name) : DevelopmentEnvironment
    {
        $developmentEnvironment = new self();
        $developmentEnvironment->raiseAndApply(
            new DevelopmentEnvironmentCreated(
                Uuid::uuid4(),
                $flowUuid,
                $user->getUsername(),
                $name,
                new \DateTime()
            )
        );

        return $developmentEnvironment;
    }

    public function applyDevelopmentEnvironmentCreated(DevelopmentEnvironmentCreated $event)
    {
        $this->uuid = $event->getDevelopmentEnvironmentUuid();
        $this->flowUuid = $event->getFlowUuid();
        $this->username = $event->getUsername();
        $this->name = $event->getName();
        $this->modificationDate = $event->getDateTime();
    }

    public function getUuid() : UuidInterface
    {
        return $this->uuid;
    }

    private function raiseAndApply($event)
    {
        $this->raise($event);
        $this->apply($event);
    }

    public function createView() : ReadModelDevelopmentEnvironment
    {
        return new ReadModelDevelopmentEnvironment(
            $this->uuid,
            $this->flowUuid,
            $this->username,
            $this->name,
            $this->modificationDate
        );
    }
}
