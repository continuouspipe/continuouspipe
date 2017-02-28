<?php

namespace AppBundle\Monolog\Processor;

use AppBundle\Model\DataCollector\UserActivityContextDataCollector;

class UserActivityContextProcessor
{
    /**
     * @var UserActivityContextDataCollector
     */
    private $contextDataCollector;

    public function __construct(UserActivityContextDataCollector $contextDataCollector)
    {
        $this->contextDataCollector = $contextDataCollector;
    }

    public function appendContext(array $record)
    {
        $context = $this->contextDataCollector->getContext();

        if (null !== ($teamSlug = $context->getTeamSlug())) {
            $record['context']['tags']['team'] = $teamSlug;
        }
        if (null !== ($flowUuid = $context->getFlowUuid())) {
            $record['context']['tags']['flow'] = (string) $flowUuid;
        }
        if (null !== ($tideUuid = $context->getTideUuid())) {
            $record['context']['tags']['tide'] = (string) $tideUuid;
        }

        return $record;
    }
}
