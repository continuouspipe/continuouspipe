<?php

namespace AppBundle\Controller;

use ContinuousPipe\Builder\Build;
use ContinuousPipe\LogStream\LogAggregator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route(service="app.controller.build_logs")
 */
class BuildLogsController
{
    /**
     * @var LogAggregator
     */
    private $logAggregator;

    /**
     * @param LogAggregator $logAggregator
     */
    public function __construct(LogAggregator $logAggregator)
    {
        $this->logAggregator = $logAggregator;
    }

    /**
     * @Route("/build/{uuid}/logs", methods={"GET"})
     */
    public function getAction(Build $build)
    {
        $logs = $this->logAggregator->getLogsFor($build);

        return new JsonResponse($logs);
    }
}
