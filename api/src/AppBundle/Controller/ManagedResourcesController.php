<?php

namespace AppBundle\Controller;

use AppBundle\Request\Managed\UsedResourcesNamespace;
use AppBundle\Request\Managed\UsedResourcesRequest;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshot;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCalculator;
use ContinuousPipe\River\Managed\Resources\Calculation\UsageSnapshotCollection;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistory;
use ContinuousPipe\River\Managed\Resources\History\ResourceUsageHistoryRepository;
use ContinuousPipe\River\Managed\Resources\ResourceUsage;
use ContinuousPipe\River\Repository\FlowNotFound;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route(service="app.controller.managed_resources")
 */
class ManagedResourcesController
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ResourceUsageHistoryRepository $usageHistoryRepository,
        FlatFlowRepository $flatFlowRepository,
        LoggerInterface $logger
    ) {
        $this->usageHistoryRepository = $usageHistoryRepository;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->logger = $logger;
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
            $this->logger->error('Cannot get flow from resource request', [
                'namespace' => $request->getNamespace()->getName(),
                'exception' => $e,
            ]);

            throw new BadRequestHttpException('Cannot get flow from resource request');
        }

        $this->usageHistoryRepository->save(new ResourceUsageHistory(
            Uuid::uuid4(),
            $flowFromNamespace->getUuid(),
            $namespace->getName(),
            new ResourceUsage(
                $request->getRequests(),
                $request->getLimits()
            ),
            new \DateTime()
        ));
    }

    /**
     * @Route("/flows/{uuid}/managed/resources", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function getFlowUsageAction(FlatFlow $flow, Request $request)
    {
        $left = new \DateTime($request->get('left', '-30days'));
        $right = new \DateTime($request->get('right', 'now'));
        $interval = new \DateInterval($request->get('internal', 'P1D'));

        $history = $this->usageHistoryRepository->findByFlow($flow->getUuid());
        $usageCalculator = new UsageSnapshotCalculator();
        $snapshotsCollection = new UsageSnapshotCollection();

        // From the history to point-in-time snapshot
        foreach ($history as $entry) {
            $usageCalculator->updateWith($entry);

            $snapshotsCollection->add(
                $entry->getDateTime(),
                $usageCalculator->snapshot()
            );
        }

        $flattenedUsage = [];

        $cursor = $left;
        while ($cursor < $right) {
            $previousUsage = $snapshotsCollection->lastBefore($cursor) ?: ResourceUsage::zero();
            $cursorEnd = clone $cursor;
            $cursorEnd->add($interval);

            if (null === ($usageInInterval = $snapshotsCollection->highestUsageInInternal($cursor, $cursorEnd))) {
                $usageInInterval = $previousUsage;
            }

            $flattenedUsage[] = [
                'datetime' => [
                    'left' => clone $cursor,
                    'right' => clone $cursorEnd,
                ],
                'usage' => $usageInInterval->max($previousUsage)->getLimits(),
            ];

            // Move cursor
            $cursor = $cursorEnd;
        }

        return $flattenedUsage;
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
}
