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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var string
     */
    private $logStreamUrl;

    /**
     * @param TideRepository     $tideRepository
     * @param EventStore         $eventStore
     * @param PaginatorInterface $paginator
     * @param string             $logStreamUrl
     */
    public function __construct(TideRepository $tideRepository, EventStore $eventStore, PaginatorInterface $paginator, $logStreamUrl)
    {
        $this->tideRepository = $tideRepository;
        $this->eventStore = $eventStore;
        $this->logStreamUrl = $logStreamUrl;
        $this->paginator = $paginator;
    }

    /**
     * @Route("/teams/{team}/flows/{flow}/tides", name="admin_tides")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow"})
     * @Template
     */
    public function listAction(Team $team, Flow $flow, Request $request)
    {
        return [
            'team' => $team,
            'flow' => $flow,
            'pagination' => $this->paginator->paginate(
                $this->tideRepository->findByFlowUuid($flow->getUuid()),
                $request->query->getInt('page', 1),
                50
            ),
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
