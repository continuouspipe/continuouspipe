<?php

namespace AdminBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\BillingProfile;
use ContinuousPipe\Billing\BillingProfile\BillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\BillingProfileRepository;
use ContinuousPipe\Model\Component\ResourcesRequest;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Managed\Resources\Calculation\AggregateResourcesRequest;
use ContinuousPipe\River\Managed\Resources\Calculation\ResourceConverter;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCalculator;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\UsageProjection\FlowUsageProjector;
use ContinuousPipe\River\Managed\Resources\UsageProjection\UsageProjector;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="admin.controller.billing")
 */
class BillingController
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;
    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;
    /**
     * @var BillingProfileRepository
     */
    private $billingProfileRepository;
    /**
     * @var UsageProjector
     */
    private $usageProjector;

    public function __construct(
        TeamRepository $teamRepository,
        FlatFlowRepository $flatFlowRepository,
        BillingProfileRepository $billingProfileRepository,
        UsageProjector $usageProjector
    ) {
        $this->teamRepository = $teamRepository;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->billingProfileRepository = $billingProfileRepository;
        $this->usageProjector = $usageProjector;
    }

    /**
     * @Route("/billing", name="admin_billing")
     * @Template
     */
    public function overviewAction(Request $request)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $left = new \DateTime($request->get('left', '-30days 00:00:00'));
        $right = new \DateTime($request->get('right', 'today 00:00:00'));
        $interval = $left->diff($right);

        $teams = $this->teamRepository->findAll();
        /** @var Team[] $teamsWithinRange */
        $teamsWithinRange = array_slice($teams, ($page - 1) * $limit, $limit);

        $overviewPerTeam = [];
        foreach ($teamsWithinRange as $team) {
            try {
                $billingProfile = $this->billingProfileRepository->findByTeam($team);
            } catch (BillingProfileNotFound $e) {
                $billingProfile = null;
            }

            $flows = $this->flatFlowRepository->findByTeam($team);
            $usage = $this->usageProjector->forFlows(
                $flows,
                $left,
                $right,
                $interval
            );

            $overviewPerTeam[$team->getSlug()] = [
                'team' => $team,
                'billingProfile' => $billingProfile,
                'flows' => $flows,
                'usage' => $this->usageSummary($usage, $billingProfile),
            ];
        }

        return [
            'overviewPerTeam' => $overviewPerTeam,
            'numberOfPages' => ceil(count($teams) / $limit),
            'page' => $page,
            'limit' => $limit,
        ];
    }

    private function usageSummary(array $usage, BillingProfile $billingProfile = null)
    {
        if (empty($usage)) {
            return [];
        }

        $resourcesCalculator = new AggregateResourcesRequest();
        $tides = 0;

        foreach ($usage[0]['entries'] as $entry) {
            $resourcesCalculator->add(new ResourcesRequest(
                $entry['usage']['cpu'],
                $entry['usage']['memory']
            ));

            $tides += $entry['usage']['tides'];
        }

        $aggregatedResources = $resourcesCalculator->toResourcesRequest();
        $usageSummary = [
            'tides' => $tides,
            'memory' => $aggregatedResources->getMemory(),
            'cpu' => $aggregatedResources->getCpu(),
        ];

        if ($billingProfile != null && null !== ($plan = $billingProfile->getPlan())) {
            if (!empty($availableTides = $plan->getMetrics()->getTides())) {
                $usageSummary['tides_percent'] = ResourceConverter::resourceToNumber($usageSummary['tides']) / $availableTides * 100;
            }

            if (!empty($availableMemory = $plan->getMetrics()->getMemory())) {
                $usageSummary['memory_percent'] = ResourceConverter::resourceToNumber($usageSummary['memory']) / ($availableMemory * 1024) * 100;
            }
        }

        return $usageSummary;
    }
}
