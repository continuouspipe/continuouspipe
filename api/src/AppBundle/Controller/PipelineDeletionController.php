<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Pipeline\Pipeline;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route(service="app.controller.pipeline_deletion")
 */
class PipelineDeletionController
{
    /**
     * @var MessageBus
     */
    private $eventBus;

    public function __construct(MessageBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @Route("/flows/{uuid}/pipeline/{pipelineUuid}", methods={"DELETE"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @Security("is_granted('ADMIN', flow)")
     */
    public function deleteAction(Flow $flow, $pipelineUuid)
    {
        $flow->deletePipelineByUuid(Uuid::fromString($pipelineUuid));

        foreach ($flow->raisedEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return new JsonResponse((object)[]);
    }
}
