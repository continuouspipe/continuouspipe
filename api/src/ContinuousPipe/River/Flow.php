<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\EventBased\ApplyAndRaiseEventCapability;
use ContinuousPipe\River\Flow\Event\FlowConfigurationUpdated;
use ContinuousPipe\River\Flow\Event\FlowCreated;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\UuidInterface;

final class Flow
{
    use ApplyAndRaiseEventCapability;

    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @var Team
     */
    private $team;

    /**
     * @var User
     */
    private $user;

    /**
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @var array
     */
    private $configuration = [];

    private function __construct()
    {
    }

    /**
     * @deprecated Should directly created the aggregate from the events
     *
     * @param FlowContext $context
     *
     * @return static
     */
    public static function fromContext(FlowContext $context)
    {
        return self::fromEvents([
            new Flow\Event\FlowCreated(
                $context->getFlowUuid(),
                $context->getTeam(),
                $context->getUser(),
                $context->getCodeRepository()
            ),
            new Flow\Event\FlowConfigurationUpdated(
                $context->getFlowUuid(),
                $context->getConfiguration()
            ),
        ]);
    }

    /**
     * @param UuidInterface  $uuid
     * @param Team           $team
     * @param User           $user
     * @param CodeRepository $codeRepository
     *
     * @return Flow
     */
    public static function create(UuidInterface $uuid, Team $team, User $user, CodeRepository $codeRepository)
    {
        $event = new FlowCreated($uuid, $team, $user, $codeRepository);

        $flow = self::fromEvents([$event]);
        $flow->raise($event);

        return $flow;
    }

    /**
     * @param array $configuration
     */
    public function update(array $configuration)
    {
        $this->raise(
            new FlowConfigurationUpdated(
                $this->uuid,
                $configuration
            )
        );
    }

    /**
     * @param FlowCreated $event
     */
    public function applyFlowCreated(FlowCreated $event)
    {
        $this->uuid = $event->getFlowUuid();
        $this->team = $event->getTeam();
        $this->user = $event->getUser();
        $this->codeRepository = $event->getCodeRepository();
    }

    /**
     * @param FlowConfigurationUpdated $event
     */
    public function applyFlowConfigurationUpdated(FlowConfigurationUpdated $event)
    {
        $this->configuration = $event->getConfiguration();
    }

    public function getUuid() : UuidInterface
    {
        return $this->uuid;
    }

    public function getTeam() : Team
    {
        return $this->team;
    }

    public function getConfiguration() : array
    {
        return $this->configuration;
    }

    public function getCodeRepository() : CodeRepository
    {
        return $this->codeRepository;
    }

    public function getUser() : User
    {
        return $this->user;
    }
}
