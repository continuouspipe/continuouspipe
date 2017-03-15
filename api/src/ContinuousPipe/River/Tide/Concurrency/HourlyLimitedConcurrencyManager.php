<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\View\TimeResolver;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class HourlyLimitedConcurrencyManager implements TideConcurrencyManager
{
    /**
     * @var TideConcurrencyManager
     */
    private $decoratedConcurrencyManager;
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var TimeResolver
     */
    private $timeResolver;
    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;
    /**
     * @var LoggerFactory
     */
    private $loggerFactory;

    public function __construct(
        TideConcurrencyManager $decoratedConcurrencyManager,
        TideRepository $tideRepository,
        TimeResolver $timeResolver,
        AuthenticatorClient $authenticatorClient,
        LoggerFactory $loggerFactory
    ) {
        $this->decoratedConcurrencyManager = $decoratedConcurrencyManager;
        $this->tideRepository = $tideRepository;
        $this->timeResolver = $timeResolver;
        $this->authenticatorClient = $authenticatorClient;
        $this->loggerFactory = $loggerFactory;
    }

    public function shouldTideStart(Tide $tide)
    {
        if ($this->hasReachedLimits($tide)) {
            $log = $this->loggerFactory->create();
            $log->child(new Text('Tides per hour limit reached.'));
            return false;
        }
        return $this->decoratedConcurrencyManager->shouldTideStart($tide);
    }

    public function postPoneTideStart(Tide $tide)
    {
        return $this->decoratedConcurrencyManager->postPoneTideStart($tide);
    }

    private function hasReachedLimits(Tide $tide)
    {
        $limit = $this->authenticatorClient->findTeamUsageLimitsBySlug($tide->getTeam()->getSlug())->getTidesPerHour();
        if (0 === $limit) {
            return false;
        }

        $oneHourAgo = $this->timeResolver->resolve()->modify('-1 hour');
        $tides = array_filter(
            $this->tideRepository->findByFlowUuid($tide->getFlowUuid())->toArray(),
            function (Tide $tide) use ($oneHourAgo) {
                return $tide->getStartDate() >= $oneHourAgo;
            }
        );

        return count($tides) > $limit;
    }
}
