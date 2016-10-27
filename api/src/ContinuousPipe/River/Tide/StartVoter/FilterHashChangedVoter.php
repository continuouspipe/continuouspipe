<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Event\GitHub\PullRequestSynchronized;
use ContinuousPipe\River\Filter\FilterHash\FilterHashEvaluator;
use ContinuousPipe\River\Filter\FilterHash\FilterHashRepository;
use ContinuousPipe\River\Tide;
use ContinuousPipe\River\View\TideRepository;
use GitHub\WebHook\Event\PullRequestEvent;

class FilterHashChangedVoter implements TideStartVoter
{
    /**
     * @var FilterHashEvaluator
     */
    private $filterHashEvaluator;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var FilterHashRepository
     */
    private $filterHashRepository;

    /**
     * @param FilterHashEvaluator  $filterHashEvaluator
     * @param TideRepository       $tideRepository
     * @param FilterHashRepository $filterHashRepository
     */
    public function __construct(FilterHashEvaluator $filterHashEvaluator, TideRepository $tideRepository, FilterHashRepository $filterHashRepository)
    {
        $this->filterHashEvaluator = $filterHashEvaluator;
        $this->tideRepository = $tideRepository;
        $this->filterHashRepository = $filterHashRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context)
    {
        return $this->tideStartedBecauseOfALabel($tide) && $this->hasChangedOfHash($tide);
    }

    /**
     * @param Tide $tide
     *
     * @return bool
     */
    private function tideStartedBecauseOfALabel(Tide $tide)
    {
        $event = $tide->getContext()->getCodeRepositoryEvent();
        if (!$event instanceof PullRequestSynchronized) {
            return false;
        }

        return $event->getEvent()->getAction() == PullRequestEvent::ACTION_LABELED;
    }

    /**
     * @param Tide $tide
     *
     * @return bool
     */
    private function hasChangedOfHash(Tide $tide)
    {
        $hash = $this->filterHashEvaluator->evaluates($tide);
        $existingHashes = $this->getFilterHashesFromOtherTides($tide);

        return !in_array($hash->getHash(), $existingHashes);
    }

    /**
     * @param Tide $tide
     *
     * @return string[]
     */
    private function getFilterHashesFromOtherTides(Tide $tide)
    {
        $tideContext = $tide->getContext();
        $otherTides = $this->tideRepository->findByCodeReference($tideContext->getFlowUuid(), $tideContext->getCodeReference());
        $hashes = [];

        foreach ($otherTides as $tide) {
            if (null !== ($hash = $this->filterHashRepository->findByTideUuid($tide->getUuid()))) {
                $hashes[] = $hash->getHash();
            }
        }

        return $hashes;
    }
}
