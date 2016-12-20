<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Tide;
use Psr\Log\LoggerInterface;

class DebugLoggingWhenVoterVotesNo implements TideStartVoter
{
    private $decoratedVoter;
    private $logger;

    public function __construct(TideStartVoter $decoratedVoter, LoggerInterface $logger)
    {
        $this->decoratedVoter = $decoratedVoter;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context)
    {
        if (false === ($vote = $this->decoratedVoter->vote($tide, $context))) {
            $this->logger->debug('Voter {voter} said no for the tide {uuid}', [
                'voter' => get_class($this->decoratedVoter),
                'uuid' => $tide,
            ]);
        }

        return $vote;
    }
}
