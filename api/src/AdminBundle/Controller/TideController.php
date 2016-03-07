<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Team\Team;
use Rhumsaa\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="admin.controller.tide")
 */
class TideController
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var string
     */
    private $logStreamUrl;

    /**
     * @param TideRepository $tideRepository
     * @param EventStore     $eventStore
     * @param string         $logStreamUrl
     */
    public function __construct(TideRepository $tideRepository, EventStore $eventStore, $logStreamUrl)
    {
        $this->tideRepository = $tideRepository;
        $this->eventStore = $eventStore;
        $this->logStreamUrl = $logStreamUrl;
    }

    /**
     * @Route("/teams/{team}/flows/{flow}/tides", name="admin_tides")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow"})
     * @Template
     */
    public function listAction(Team $team, Flow $flow)
    {
        return [
            'team' => $team,
            'flow' => $flow,
            'tides' => $this->tideRepository->findByFlowUuid($flow->getUuid()),
        ];
    }

    /**
     * @Route("/teams/{team}/flows/{flow}/tides/{uuid}", name="admin_tide")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow"})
     * @Template
     */
    public function showAction(Team $team, Flow $flow, $uuid)
    {
        $tideUuid = Uuid::fromString($uuid);
        $tide = $this->tideRepository->find($tideUuid);

        return [
            'team' => $team,
            'flow' => $flow,
            'tide' => $tide,
            'tideLogsUrl' => sprintf('%s/#log/%s', $this->logStreamUrl, $tide->getLogId()),
            'events' => $this->eventStore->findByTideUuidWithMetadata($tideUuid),
        ];
    }
}
