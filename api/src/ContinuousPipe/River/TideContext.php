<?php

namespace ContinuousPipe\River;

use LogStream\Log;
use Rhumsaa\Uuid\Uuid;

class TideContext extends FlowContext
{
    const CODE_REFERENCE_KEY = 'codeReference';
    const TIDE_UUID_KEY = 'tideUuid';
    const TIDE_LOG_KEY = 'tideLog';

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
     * @param FlowContext   $flowContext
     * @param Uuid          $tideUuid
     * @param CodeReference $codeReference
     * @param Log           $log
     *
     * @return TideContext
     */
    public static function createTide(FlowContext $flowContext, Uuid $tideUuid, CodeReference $codeReference, Log $log)
    {
        $context = new self($flowContext);
        $context->set(self::TIDE_UUID_KEY, $tideUuid);
        $context->set(self::CODE_REFERENCE_KEY, $codeReference);
        $context->set(self::TIDE_LOG_KEY, $log);

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
}
