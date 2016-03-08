<?php

namespace ContinuousPipe\River\Recover\TimedOutTides;

use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Rhumsaa\Uuid\Uuid;

class FilteredTimedOutTideRepository implements TimedOutTideRepository
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var int
     */
    private $tideTimeout;

    /**
     * @param TideRepository $tideRepository
     * @param int            $tideTimeout
     */
    public function __construct(TideRepository $tideRepository, $tideTimeout)
    {
        $this->tideRepository = $tideRepository;
        $this->tideTimeout = $tideTimeout;
    }

    /**
     * @param Uuid $uuid
     *
     * @return Tide[]
     */
    public function findByFlow(Uuid $uuid)
    {
        $runningTides = $this->tideRepository->findRunningByFlowUuid($uuid);

        return array_filter($runningTides, function (Tide $tide) {
            $tideDateTime = $tide->getStartDate() ?: $tide->getCreationDate();
            $runningSeconds = time() - $tideDateTime->getTimestamp();

            return $runningSeconds * 1000 > $this->tideTimeout;
        });
    }
}
