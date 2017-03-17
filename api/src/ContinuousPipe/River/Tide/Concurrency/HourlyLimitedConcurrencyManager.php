<?php

namespace ContinuousPipe\River\Tide\Concurrency;

use ContinuousPipe\River\View\TimeResolver;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use LogStream\LoggerFactory;
use LogStream\Node\Text;
use Psr\Log\LoggerInterface;

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
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var integer
     */
    private $limit;

    public function __construct(
        TideConcurrencyManager $decoratedConcurrencyManager,
        TideRepository $tideRepository,
        TimeResolver $timeResolver,
        AuthenticatorClient $authenticatorClient,
        LoggerFactory $loggerFactory,
        LoggerInterface $logger
    ) {
        $this->decoratedConcurrencyManager = $decoratedConcurrencyManager;
        $this->tideRepository = $tideRepository;
        $this->timeResolver = $timeResolver;
        $this->authenticatorClient = $authenticatorClient;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldTideStart(Tide $tide)
    {
        if ($this->hasReachedLimits($tide)) {
            $log = $this->loggerFactory->fromId($tide->getLogId());
            $log->child(new Text(sprintf('Tides per hour limit of %d reached.', $this->getLimitByTide($tide))));
            return false;
        }
        return $this->decoratedConcurrencyManager->shouldTideStart($tide);
    }

    /**
     * {@inheritdoc}
     */
    public function postPoneTideStart(Tide $tide)
    {
        return $this->decoratedConcurrencyManager->postPoneTideStart($tide);
    }

    private function hasReachedLimits(Tide $tide)
    {
        if (0 === ($limit = $this->getLimitByTide($tide))) {
            return false;
        }

        $startedTidesCount = $this->tideRepository->countStartedTidesByFlowSince(
            $tide->getFlowUuid(),
            $this->timeResolver->resolve()->modify('-1 hour')
        );

        return $startedTidesCount > $limit;
    }

    private function getLimitByTide(Tide $tide) : int
    {
        if (isset($this->limit)) {
            return $this->limit;
        }

        try {
            $this->limit = $this->authenticatorClient->findTeamUsageLimitsBySlug($tide->getTeam()->getSlug())->getTidesPerHour();
        } catch (\Exception $exception) {
            $this->logger->warning(
                'Can\'t get team usage limits',
                ['exception' => $exception, 'tide' => $tide, 'team' => $tide->getTeam()->getSlug()]
            );
            $this->limit = 0;
        } finally {
            return $this->limit;
        }
    }
}
