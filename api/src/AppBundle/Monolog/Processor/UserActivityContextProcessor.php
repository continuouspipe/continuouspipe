<?php

namespace AppBundle\Monolog\Processor;

use AppBundle\Model\DataCollector\UserActivityContextProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserActivityContextProcessor
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function appendContext(array $record)
    {
        $context = $this->getUserActivityContextProvider()->getContext();

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

    private function getUserActivityContextProvider() : UserActivityContextProvider
    {
        return $this->container->get('river.data_collector.user_activity_context_aggregated');
    }
}
