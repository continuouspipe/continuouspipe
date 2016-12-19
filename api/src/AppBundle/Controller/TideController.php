<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CommitResolver;
use ContinuousPipe\River\CodeRepository\CommitResolverException;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Pipeline\Command\GenerateTides;
use ContinuousPipe\River\Pipeline\TideGenerationRequest;
use ContinuousPipe\River\Pipeline\TideGenerationTrigger;
use ContinuousPipe\River\Recover\CancelTides\Command\CancelTideCommand;
use ContinuousPipe\River\Tide\ExternalRelation\ExternalRelationResolver;
use ContinuousPipe\River\Tide\Request\TideCreationRequest;
use ContinuousPipe\River\Tide\TideSummaryCreator;
use ContinuousPipe\River\TideFactory;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\User\User;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @var ExternalRelationResolver
     */
    private $externalRelationResolver;

    /**
     * @var CommitResolver
     */
    private $commitResolver;

    /**
     * @param TideRepository           $tideRepository
     * @param ValidatorInterface       $validator
     * @param TideFactory              $tideFactory
     * @param MessageBus               $eventBus
     * @param TideSummaryCreator       $tideSummaryCreator
     * @param PaginatorInterface       $paginator
     * @param MessageBus               $commandBus
     * @param ExternalRelationResolver $externalRelationResolver
     * @param CommitResolver           $commitResolver
     */
    public function __construct(TideRepository $tideRepository, ValidatorInterface $validator, TideFactory $tideFactory, MessageBus $eventBus, TideSummaryCreator $tideSummaryCreator, PaginatorInterface $paginator, MessageBus $commandBus, ExternalRelationResolver $externalRelationResolver, CommitResolver $commitResolver)
    {
        $this->tideRepository = $tideRepository;
        $this->validator = $validator;
        $this->tideFactory = $tideFactory;
        $this->eventBus = $eventBus;
        $this->tideSummaryCreator = $tideSummaryCreator;
        $this->paginator = $paginator;
        $this->commandBus = $commandBus;
        $this->externalRelationResolver = $externalRelationResolver;
        $this->commitResolver = $commitResolver;
    }

    /**
     * Get tide by flow.
     *
     * @Route("/flows/{uuid}/tides", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function findByFlowAction(Request $request, Flow\Projections\FlatFlow $flow)
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
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @ParamConverter("creationRequest", converter="fos_rest.request_body")
     * @ParamConverter("user", converter="user")
     * @Security("is_granted('CREATE_TIDE', flow)")
     * @View(statusCode=201)
     */
    public function createAction(Flow\Projections\FlatFlow $flow, TideCreationRequest $creationRequest, User $user)
    {
        $errors = $this->validator->validate($creationRequest);
        if ($errors->count() > 0) {
            return new JsonResponse([
                'error' => $errors->get(0)->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (empty($creationRequest->getSha1())) {
            try {
                $headCommit = $this->commitResolver->getHeadCommitOfBranch($flow, $creationRequest->getBranch());
            } catch (CommitResolverException $e) {
                return new JsonResponse([
                    'error' => $e->getMessage(),
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $creationRequest = new TideCreationRequest(
                $creationRequest->getBranch(),
                $headCommit
            );
        }

        $generationUuid = Uuid::uuid4();

        $this->commandBus->handle(new GenerateTides(
            new TideGenerationRequest(
                $generationUuid,
                $flow,
                new CodeReference(
                    $flow->getRepository(),
                    $creationRequest->getSha1(),
                    $creationRequest->getBranch()
                ),
                TideGenerationTrigger::user($user)
            )
        ));

        return $this->tideRepository->findByGenerationUuid($flow->getUuid(), $generationUuid);
    }

    /**
     * Get a tide by its UUID.
     *
     * @Route("/tides/{uuid}", methods={"GET"})
     * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
     * @Security("is_granted('READ', tide)")
     * @View
     */
    public function getAction(Tide $tide)
    {
        return $tide;
    }

    /**
     * Get summary of a the given tide.
     *
     * @Route("/tides/{uuid}/summary", methods={"GET"})
     * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
     * @Security("is_granted('READ', tide)")
     * @View
     */
    public function summaryAction(Tide $tide)
    {
        return $this->tideSummaryCreator->fromTide($tide);
    }

    /**
     * @Route("/tides/{uuid}/external-relations", methods={"GET"})
     * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
     * @Security("is_granted('READ', tide)")
     * @View
     */
    public function externalRelationsAction(Tide $tide)
    {
        return $this->externalRelationResolver->getRelations($tide->getUuid());
    }

    /**
     * Cancel the given tide.
     *
     * @Route("/tides/{uuid}/cancel", methods={"POST"})
     * @ParamConverter("tide", converter="tide", options={"identifier"="uuid"})
     * @Security("is_granted('READ', tide)")
     * @View
     */
    public function cancelAction(Tide $tide)
    {
        $this->commandBus->handle(new CancelTideCommand($tide->getUuid()));
    }
}
