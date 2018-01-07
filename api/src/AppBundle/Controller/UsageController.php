<?php

namespace AppBundle\Controller;

use AppBundle\Request\Managed\UsedResourcesNamespace;
use AppBundle\Request\Managed\UsedResourcesRequest;
use ContinuousPipe\Model\Component\ResourcesRequest;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Managed\Resources\Calculation\Interval;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshot;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCalculator;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCollection;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;
use ContinuousPipe\River\Managed\Resources\UsageProjection\FlowUsageProjector;
use ContinuousPipe\River\Managed\Resources\UsageProjection\UsageProjector;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use ContinuousPipe\TimeResolver\TimeResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route(service="app.controller.usage")
 */
class UsageController
{
    /**
     * @var ResourceUsageHistoryRepository
     */
    private $usageHistoryRepository;
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;
    /**
     * @var TideRepository
     */
    private $tideRepository;
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TimeResolver
     */
    private $timeResolver;
    /**
     * @var FlowUsageProjector
     */
    private $flowUsageProjector;
    /**
     * @var UsageProjector
     */
    private $usageProjector;

    public function __construct(
        ResourceUsageHistoryRepository $usageHistoryRepository,
        FlatFlowRepository $flatFlowRepository,
        TideRepository $tideRepository,
        TeamRepository $teamRepository,
        LoggerInterface $logger,
        TimeResolver $timeResolver,
        FlowUsageProjector $flowUsageProjector,
        UsageProjector $usageProjector
    ) {
        $this->usageHistoryRepository = $usageHistoryRepository;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->tideRepository = $tideRepository;
        $this->logger = $logger;
        $this->teamRepository = $teamRepository;
        $this->timeResolver = $timeResolver;
        $this->flowUsageProjector = $flowUsageProjector;
        $this->usageProjector = $usageProjector;
    }

    /**
     * @Route("/managed/resources", methods={"POST"})
     * @ParamConverter("request", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function addAction(UsedResourcesRequest $request)
    {
        if (null === ($namespace = $request->getNamespace())) {
            throw new BadRequestHttpException('No namespace in resource request');
        }

        try {
            $flowFromNamespace = $this->flowFromNamespace($namespace);
        } catch (\InvalidArgumentException $e) {
            $this->logger->info('Cannot get flow from resource request', [
                'namespace' => $request->getNamespace()->getName(),
                'exception' => $e,
            ]);

            throw new BadRequestHttpException('Cannot get flow from resource request');
        }

        $this->usageHistoryRepository->save(new ResourceUsageHistory(
            Uuid::uuid4(),
            $flowFromNamespace->getUuid(),
            $namespace->getName(),
            $this->usageFromRequest($request),
            $this->timeResolver->resolve()
        ));
    }

    /**
     * @Route("/flows/{uuid}/usage/resources", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function getResourcesUsageAction(FlatFlow $flow, Request $request)
    {
        return $this->flowUsageProjector->getResourcesUsage(
            $flow,
            new \DateTime($request->get('left', '-30days')),
            new \DateTime($request->get('right', 'now')),
            new \DateInterval($request->get('interval', 'P1D'))
        );
    }

    /**
     * @Route("/flows/{uuid}/usage/tides", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function getTidesUsageAction(FlatFlow $flow, Request $request)
    {
        return $this->flowUsageProjector->getTideUsage(
            $flow,
            new \DateTime($request->get('left', '-30days')),
            new \DateTime($request->get('right', 'now')),
            new \DateInterval($request->get('interval', 'P1D'))
        );
    }

    /**
     * @Route("/usage/aggregated", methods={"GET"})
     * @View
     */
    public function getAggregatedUsageAction(Request $request)
    {
        $left = new \DateTime($request->get('left', '-30days'));
        $right = new \DateTime($request->get('right', 'now'));
        $interval = new \DateInterval($request->get('interval', 'P1D'));

        $teams = array_map(function (string $teamSlug) {
            return $this->teamRepository->find($teamSlug);
        }, explode(',', $request->get('teams', '')));

        if (empty($teams)) {
            throw new BadRequestHttpException('No teams requested');
        }

        $flows = array_reduce($teams, function (array $carry, Team $team) {
            return array_merge($carry, $this->flatFlowRepository->findByTeam($team));
        }, []);

        return $this->usageProjector->forFlows($flows, $left, $right, $interval);
    }

    private function flowFromNamespace(UsedResourcesNamespace $namespace) : FlatFlow
    {
        $labels = $namespace->getLabels();

        if (isset($labels['flow'])) {
            try {
                return $this->flatFlowRepository->find(Uuid::fromString($labels['flow']));
            } catch (FlowNotFound $e) {
                throw new \InvalidArgumentException('Can\'t get flow from namespace\'s label: '.$labels['flow'], $e->getCode(), $e);
            }
        }

        throw new \InvalidArgumentException('No label on the namespace');
    }

    private function usageFromRequest(UsedResourcesRequest $request): ResourceUsage
    {
        return new ResourceUsage(
            $this->resourcesOrZero($request->getRequests()),
            $this->resourcesOrZero($request->getLimits())
        );
    }

    private function resourcesOrZero(ResourcesRequest $request = null)
    {
        if (null === $request) {
            return new ResourcesRequest(0, 0);
        }

        return new ResourcesRequest(
            $request->getCpu() ?: 0,
            $request->getMemory() ?: 0
        );
    }
}
