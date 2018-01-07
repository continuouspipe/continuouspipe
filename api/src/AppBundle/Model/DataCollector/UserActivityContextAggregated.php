<?php

namespace AppBundle\Model\DataCollector;

use ContinuousPipe\UserActivity\UserActivityContext;

class UserActivityContextAggregated implements UserActivityContextProvider
{
    /**
     * @var UserActivityContextProvider[]
     */
    private $userActivityContextProviders;

    /**
     * UserActivityContextAggregated constructor.
     *
     * @param array $userActivityContextProviders Array of UserActivityContextProvider objects.
     */
    public function __construct(array $userActivityContextProviders)
    {
        $this->userActivityContextProviders = $userActivityContextProviders;
    }

    public function getContext(): UserActivityContext
    {
        $context = new UserActivityContext();

        foreach ($this->userActivityContextProviders as $contextProvider) {
            if (null !== ($teamSlug = $contextProvider->getContext()->getTeamSlug())) {
                $context->setTeamSlug($teamSlug);
            }
            if (null !== ($flowUuid = $contextProvider->getContext()->getFlowUuid())) {
                $context->setFlowUuid($flowUuid);
            }
            if (null !== ($tideUuid = $contextProvider->getContext()->getTideUuid())) {
                $context->setTideUuid($tideUuid);
            }
        }

        return $context;
    }
}
