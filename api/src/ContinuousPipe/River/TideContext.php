<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use LogStream\Log;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TideContext implements Context
{
    const CODE_REFERENCE_KEY = 'codeReference';
    const TIDE_UUID_KEY = 'tideUuid';
    const TIDE_LOG_KEY = 'tideLog';
    const CONFIGURATION_KEY = 'config';
    const CODE_REPOSITORY_EVENT_KEY = 'codeRepositoryEvent';
    const FLOW_UUID_KEY = 'flowUuid';
    const TEAM_KEY = 'team';
    const USER_KEY = 'user';

    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context = null)
    {
        $this->context = $context ?: new ArrayContext();
    }

    /**
     * @param Uuid                $tideUuid
     * @param CodeReference       $codeReference
     * @param Log                 $log
     * @param array               $configuration
     * @param CodeRepositoryEvent $codeRepositoryEvent
     *
     * @return TideContext
     */
    public static function createTide(UuidInterface $flowUuid, Team $team, User $user, Uuid $tideUuid, CodeReference $codeReference, Log $log, array $configuration, CodeRepositoryEvent $codeRepositoryEvent = null)
    {
        $context = new self();
        $context->set(self::FLOW_UUID_KEY, $flowUuid);
        $context->set(self::TEAM_KEY, $team);
        $context->set(self::USER_KEY, $user);
        $context->set(self::TIDE_UUID_KEY, $tideUuid);
        $context->set(self::CODE_REFERENCE_KEY, $codeReference);
        $context->set(self::TIDE_LOG_KEY, $log);
        $context->set(self::CONFIGURATION_KEY, json_encode($configuration));
        $context->set(self::CODE_REPOSITORY_EVENT_KEY, $codeRepositoryEvent);

        return $context;
    }

    /**
     * @return CodeReference
     */
    public function getCodeReference()
    {
        return $this->context->get(self::CODE_REFERENCE_KEY);
    }

    /**
     * @return CodeRepository
     */
    public function getCodeRepository()
    {
        return $this->getCodeReference()->getRepository();
    }

    /**
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->context->get(self::TIDE_UUID_KEY);
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid()
    {
        return $this->context->get(self::FLOW_UUID_KEY);
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->context->get(self::TEAM_KEY);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->context->get(self::USER_KEY);
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->context->get(self::TIDE_LOG_KEY);
    }

    /***
     * @return array
     */
    public function getConfiguration()
    {
        return json_decode($this->context->get(self::CONFIGURATION_KEY), true);
    }

    /**
     * @return CodeRepositoryEvent|null
     */
    public function getCodeRepositoryEvent()
    {
        try {
            return $this->context->get(self::CODE_REPOSITORY_EVENT_KEY);
        } catch (ContextKeyNotFound $e) {
            return;
        }
    }

    /**
     * @return array
     */
    public function getBag()
    {
        return $this->context->getBag();
    }

    public function has($key)
    {
        return $this->context->has($key);
    }

    public function get($key)
    {
        return $this->context->get($key);
    }

    public function set($key, $value)
    {
        return $this->context->set($key, $value);
    }
}
