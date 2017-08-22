<?php

namespace AdminBundle\Controller;

use ContinuousPipe\River\ClusterPolicies\Resources\ResourceCalculator;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;
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
     * @var Flow\EnvironmentClient
     */
    private $environmentClient;

    /**
     * @var string
     */
    private $uiUrl;

    /**
     * @param TideRepository $tideRepository
     * @param EventStore $eventStore
     * @param PaginatorInterface $paginator
     * @param Flow\EnvironmentClient $environmentClient
     * @param string $uiUrl
     */
    public function __construct(
        TideRepository $tideRepository,
        EventStore $eventStore,
        PaginatorInterface $paginator,
        Flow\EnvironmentClient $environmentClient,
        string $uiUrl
    ) {
        $this->tideRepository = $tideRepository;
        $this->eventStore = $eventStore;
        $this->paginator = $paginator;
        $this->environmentClient = $environmentClient;
        $this->uiUrl = $uiUrl;
    }

    /**
     * @Route("/teams/{team}/flows/{flow}/tides", name="admin_tides")
     * @ParamConverter("team", converter="team", options={"slug"="team"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow", "flat"=true})
     * @Template
     */
    public function listAction(Team $team, Flow\Projections\FlatFlow $flow, Request $request)
    {
        $environments = $this->environmentClient->findByFlow($flow);
        $usage = ResourceCalculator::sumEnvironmentResources($environments);

        return [
            'team' => $team,
            'flow' => $flow,
            'usage' => $usage,
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
     * @ParamConverter("flow", converter="flow", options={"identifier"="flow", "flat"=true})
     * @Template
     */
    public function showAction(Team $team, Flow\Projections\FlatFlow $flow, $uuid)
    {
        $tideUuid = Uuid::fromString($uuid);
        $tide = $this->tideRepository->find($tideUuid);
        $logsUrl = sprintf(
            '%s/team/%s/%s/%s/logs',
            $this->uiUrl,
            $tide->getTeam()->getSlug(),
            (string) $tide->getFlowUuid(),
            (string) $tide->getUuid()
        );

        return [
            'team' => $team,
            'flow' => $flow,
            'tide' => $tide,
            'tideLogsUrl' => $logsUrl,
            'events' => $this->eventStore->findByTideUuidWithMetadata($tideUuid),
        ];
    }
}
