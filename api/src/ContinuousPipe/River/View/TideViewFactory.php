<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\Repository\TideRepository;
use Ramsey\Uuid\UuidInterface;

class TideViewFactory
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

    public function create(UuidInterface $tideUuid)
    {
        $view = Tide::create(
            $event->getTideUuid(),
            $tideContext->getFlowUuid(),
            $tideContext->getCodeReference(),
            $tideContext->getLog(),
            $tideContext->getTeam(),
            $tideContext->getUser(),
            $tideContext->getConfiguration(),
            $this->timeResolver->resolve()
        );

        $view->setStatus(Tide::STATUS_PENDING);


    }
}
