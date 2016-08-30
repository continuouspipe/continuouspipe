<?php

namespace ContinuousPipe\River\Filter\FilterHash\EventListener\TideCreated;

use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Filter\FilterHash\FilterHashEvaluator;
use ContinuousPipe\River\Filter\FilterHash\FilterHashRepository;
use ContinuousPipe\River\Repository\TideRepository;

class StoreFilterHash
{
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var FilterHashRepository
     */
    private $filterHashRepository;
    /**
     * @var FilterHashEvaluator
     */
    private $filterHashEvaluator;

    /**
     * @param TideRepository       $tideRepository
     * @param FilterHashRepository $filterHashRepository
     * @param FilterHashEvaluator  $filterHashEvaluator
     */
    public function __construct(TideRepository $tideRepository, FilterHashRepository $filterHashRepository, FilterHashEvaluator $filterHashEvaluator)
    {
        $this->tideRepository = $tideRepository;
        $this->filterHashRepository = $filterHashRepository;
        $this->filterHashEvaluator = $filterHashEvaluator;
    }

    /**
     * @param TideCreated $event
     */
    public function notify(TideCreated $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());
        $hash = $this->filterHashEvaluator->evaluates($tide);

        $this->filterHashRepository->save($hash);
    }
}
