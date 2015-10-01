<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Tide\Request\TideCreationRequest;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\TideRepository;
use Rhumsaa\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route(service="app.controller.tide")
 */
class TideController
{
    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var TideFactory
     */
    private $tideFactory;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param TideRepository     $tideRepository
     * @param ValidatorInterface $validator
     * @param TideFactory        $tideFactory
     * @param MessageBus         $eventBus
     */
    public function __construct(TideRepository $tideRepository, ValidatorInterface $validator, TideFactory $tideFactory, MessageBus $eventBus)
    {
        $this->tideRepository = $tideRepository;
        $this->validator = $validator;
        $this->tideFactory = $tideFactory;
        $this->eventBus = $eventBus;
    }

    /**
     * Get tide by flow.
     *
     * @Route("/flows/{uuid}/tides", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @View
     */
    public function findByFlowAction(Flow $flow)
    {
        return $this->tideRepository->findByFlow($flow);
    }

    /**
     * Get tide by flow.
     *
     * @Route("/flows/{uuid}/tides", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(Flow $flow, TideCreationRequest $creationRequest)
    {
        $errors = $this->validator->validate($creationRequest);
        if ($errors->count() > 0) {
            return new JsonResponse([
                'error' => $errors->get(0)->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $tide = $this->tideFactory->createFromCreationRequest($flow, $creationRequest);
        } catch (CommitResolverException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        foreach ($tide->popNewEvents() as $event) {
            $this->eventBus->handle($event);
        }

        return $this->tideRepository->find($tide->getUuid());
    }

    /**
     * Get a tide by its UUID.
     *
     * @Route("/tides/{uuid}", methods={"GET"})
     * @View
     */
    public function getAction($uuid)
    {
        return $this->tideRepository->find(Uuid::fromString($uuid));
    }
}
