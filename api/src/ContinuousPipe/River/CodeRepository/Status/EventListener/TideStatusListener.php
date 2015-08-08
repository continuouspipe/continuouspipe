<?php

namespace ContinuousPipe\River\CodeRepository\Status\EventListener;

use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Repository\TideRepository;

class TideStatusListener
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var CodeStatusUpdater
     */
    private $codeStatusUpdater;

    /**
     * @param TideRepository    $tideRepository
     * @param CodeStatusUpdater $codeStatusUpdater
     */
    public function __construct(TideRepository $tideRepository, CodeStatusUpdater $codeStatusUpdater)
    {
        $this->tideRepository = $tideRepository;
        $this->codeStatusUpdater = $codeStatusUpdater;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());

        if ($event instanceof TideSuccessful) {
            $this->codeStatusUpdater->success($tide);
        } elseif ($event instanceof TideCreated) {
            $this->codeStatusUpdater->pending($tide);
        } elseif ($event instanceof TideFailed) {
            $this->codeStatusUpdater->failure($tide);
        }
    }
}
