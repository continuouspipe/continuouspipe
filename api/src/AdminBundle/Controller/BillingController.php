<?php

namespace AdminBundle\Controller;

use ContinuousPipe\Billing\BillingProfile\BillingProfileNotFound;
use ContinuousPipe\Billing\BillingProfile\BillingProfileRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Managed\Resources\UsageProjection\UsageSummaryProjector;
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
     * @var UsageSummaryProjector
     */
    private $usageProjector;

    public function __construct(
        TeamRepository $teamRepository,
        FlatFlowRepository $flatFlowRepository,
        BillingProfileRepository $billingProfileRepository,
        UsageSummaryProjector $usageProjector
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
                $interval,
                $billingProfile
            );

            $overviewPerTeam[$team->getSlug()] = [
                'team' => $team,
                'billingProfile' => $billingProfile,
                'flows' => $flows,
                'usage' => $usage,
            ];
        }

        return [
            'overviewPerTeam' => $overviewPerTeam,
            'numberOfPages' => ceil(count($teams) / $limit),
            'page' => $page,
            'limit' => $limit,
        ];
    }
}
