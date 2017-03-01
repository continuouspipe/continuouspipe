<?php

namespace AppBundle\Monolog\Processor;

use AppBundle\Model\DataCollector\UserActivityContextProvider;

class UserActivityContextProcessor
{
    /**
     * @var UserActivityContextProvider
     */
    private $userActivityContextProvider;

    public function __construct(UserActivityContextProvider $userActivityContextProvider)
    {
        $this->userActivityContextProvider = $userActivityContextProvider;
    }

    public function appendContext(array $record)
    {
        $context = $this->userActivityContextProvider->getContext();

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
