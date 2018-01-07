<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Pipeline\PipelineNotFound;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View as ViewResponse;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @View(statusCode=204)
     */
    public function deleteAction(Flow $flow, $pipelineUuid)
    {
        try {
            $flow->deletePipelineByUuid(Uuid::fromString($pipelineUuid));

            foreach ($flow->raisedEvents() as $event) {
                $this->eventBus->handle($event);
            }
        } catch (PipelineNotFound $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
