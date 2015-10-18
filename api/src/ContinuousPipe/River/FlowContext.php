<?php

namespace ContinuousPipe\River;

use ContinuousPipe\Security\User\User;
use Rhumsaa\Uuid\Uuid;

class FlowContext implements Context
{
    const CODE_REPOSITORY_KEY = 'codeRepository';
    const USER_KEY = 'user';
    const FLOW_UUID_KEY = 'flowUuid';
    const CONFIGURATION_KEY = 'configuration';

    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param Uuid           $flowUuid
     * @param User           $user
     * @param CodeRepository $codeRepository
     * @param array          $configuration
     *
     * @return FlowContext
     */
    public static function createFlow(Uuid $flowUuid, User $user, CodeRepository $codeRepository, array $configuration)
    {
        $context = new ArrayContext();
        $context->set(self::FLOW_UUID_KEY, $flowUuid);
        $context->set(self::USER_KEY, $user);
        $context->set(self::CODE_REPOSITORY_KEY, $codeRepository);
        $context->set(self::CONFIGURATION_KEY, json_encode($configuration));

        return new self($context);
    }

    /**
     * @return CodeRepository
     */
    public function getCodeRepository()
    {
        return $this->context->get(self::CODE_REPOSITORY_KEY);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->context->get(self::USER_KEY);
    }

    /**
     * @return Uuid
     */
    public function getFlowUuid()
    {
        return $this->context->get(self::FLOW_UUID_KEY);
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        try {
            return json_decode($this->get(self::CONFIGURATION_KEY), true);
        } catch (ContextKeyNotFound $e) {
            return [];
        }
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
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
