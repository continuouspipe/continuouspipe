<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Recover\CancelTides\Command\CancelTideCommand;
use ContinuousPipe\River\Tide\Request\TideCreationRequest;
use ContinuousPipe\River\Tide\TideSummaryCreator;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\TideRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Rhumsaa\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @var TideSummaryCreator
     */
    private $tideSummaryCreator;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @param TideRepository     $tideRepository
     * @param ValidatorInterface $validator
     * @param TideFactory        $tideFactory
     * @param MessageBus         $eventBus
     * @param TideSummaryCreator $tideSummaryCreator
     * @param PaginatorInterface $paginator
     * @param MessageBus         $commandBus
     */
    public function __construct(TideRepository $tideRepository, ValidatorInterface $validator, TideFactory $tideFactory, MessageBus $eventBus, TideSummaryCreator $tideSummaryCreator, PaginatorInterface $paginator, MessageBus $commandBus)
    {
        $this->tideRepository = $tideRepository;
        $this->validator = $validator;
        $this->tideFactory = $tideFactory;
        $this->eventBus = $eventBus;
        $this->tideSummaryCreator = $tideSummaryCreator;
        $this->paginator = $paginator;
        $this->commandBus = $commandBus;
    }

    /**
     * Get tide by flow.
     *
     * @Route("/flows/{uuid}/tides", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid"})
     * @View
     */
    public function findByFlowAction(Request $request, Flow $flow)
    {
        /** @var SlidingPagination $paginated */
        $paginated = $this->paginator->paginate(
            $this->tideRepository->findByFlowUuid($flow->getUuid()),
            $request->get('page', 1),
            $request->get('limit', 100)
        );

        return $paginated->getItems();
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

    /**
     * Get summary of a the given tide.
     *
     * @Route("/tides/{uuid}/summary", methods={"GET"})
     * @View
     */
    public function summaryAction($uuid)
    {
        return $this->tideSummaryCreator->fromTide(
            $this->tideRepository->find(Uuid::fromString($uuid))
        );
    }

    /**
     * Cancel the given tide.
     *
     * @Route("/tides/{uuid}/cancel", methods={"POST"})
     * @View
     */
    public function cancelAction($uuid)
    {
        $this->commandBus->handle(new CancelTideCommand(Uuid::fromString($uuid)));
    }
}
