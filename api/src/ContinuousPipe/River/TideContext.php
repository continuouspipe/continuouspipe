<?php

namespace ContinuousPipe\River;

use ContinuousPipe\River\Event\CodeRepositoryEvent;
use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class TideContext extends FlowContext
{
    const CODE_REFERENCE_KEY = 'codeReference';
    const TIDE_UUID_KEY = 'tideUuid';
    const TIDE_LOG_KEY = 'tideLog';
    const CONFIGURATION_KEY = 'config';
    const CODE_REPOSITORY_EVENT_KEY = 'codeRepositoryEvent';

    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->context = $context;
    }

    /**
     * @param FlowContext         $flowContext
     * @param Uuid                $tideUuid
     * @param CodeReference       $codeReference
     * @param Log                 $log
     * @param array               $configuration
     * @param CodeRepositoryEvent $codeRepositoryEvent
     *
     * @return TideContext
     */
    public static function createTide(FlowContext $flowContext, Uuid $tideUuid, CodeReference $codeReference, Log $log, array $configuration, CodeRepositoryEvent $codeRepositoryEvent = null)
    {
        $context = new self($flowContext);
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
     * @return Uuid
     */
    public function getTideUuid()
    {
        return $this->context->get(self::TIDE_UUID_KEY);
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
}
