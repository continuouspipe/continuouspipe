<?php

namespace ContinuousPipe\River\CodeRepository\Status\EventListener;

use ContinuousPipe\River\CodeRepository\CodeStatusException;
use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\Event\TideCreated;
use ContinuousPipe\River\Event\TideEvent;
use ContinuousPipe\River\Event\TideFailed;
use ContinuousPipe\River\Event\TideSuccessful;
use ContinuousPipe\River\Repository\TideRepository;
use ContinuousPipe\River\Tide;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

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
     * @var LoggerFactory
     */
    private $loggerFactory;

    /**
     * @param TideRepository    $tideRepository
     * @param CodeStatusUpdater $codeStatusUpdater
     * @param LoggerFactory     $loggerFactory
     */
    public function __construct(TideRepository $tideRepository, CodeStatusUpdater $codeStatusUpdater, LoggerFactory $loggerFactory)
    {
        $this->tideRepository = $tideRepository;
        $this->codeStatusUpdater = $codeStatusUpdater;
        $this->loggerFactory = $loggerFactory;
    }

    /**
     * @param TideEvent $event
     */
    public function notify(TideEvent $event)
    {
        $tide = $this->tideRepository->find($event->getTideUuid());

        try {
            $this->updateTideStatus($event, $tide);
        } catch (CodeStatusException $e) {
            $logger = $this->loggerFactory->from($tide->getContext()->getLog());
            $logger->child(new Text($e->getMessage()));
        }
    }

    /**
     * @param TideEvent $event
     * @param Tide      $tide
     *
     * @throws CodeStatusException
     */
    private function updateTideStatus(TideEvent $event, Tide $tide)
    {
        if ($event instanceof TideSuccessful) {
            $this->codeStatusUpdater->success($tide);
        } elseif ($event instanceof TideCreated) {
            $this->codeStatusUpdater->pending($tide);
        } elseif ($event instanceof TideFailed) {
            $this->codeStatusUpdater->failure($tide);
        }
    }
}
