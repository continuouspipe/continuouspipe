<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\TideConfigurationException;
use ContinuousPipe\River\View\TideRepository;

class TideAlreadyCreatedVoter implements TideStartVoter
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param TideRepository $tideRepository
     */
    public function __construct(TideRepository $tideRepository)
    {
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context)
    {
        $matchingTides = $this->tideRepository->findByCodeReference($tide->getContext()->getCodeReference());

        return count($matchingTides) == 0;
    }
}
