<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Event\GitHub\IntegrationInstallationDeleted;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use GitHub\WebHook\Event\IntegrationInstallationEvent;
use GitHub\WebHook\GitHubRequest;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.github")
 */
class GitHubController
{
    /**
     * @var MessageBus
     */
    private $commandBus;

    /**
     * @var FlatFlowRepository
     */
    private $flowRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @param MessageBus $commandBus
     * @param FlatFlowRepository $flowRepository
     * @param LoggerInterface $logger
     * @param MessageBus $eventBus
     */
    public function __construct(
        MessageBus $commandBus,
        FlatFlowRepository $flowRepository,
        LoggerInterface $logger,
        MessageBus $eventBus
    ) {
        $this->commandBus = $commandBus;
        $this->flowRepository = $flowRepository;
        $this->logger = $logger;
        $this->eventBus = $eventBus;
    }

    /**
     * @Route("/web-hook/github/{uuid}", methods={"POST"}, name="web_hook_github")
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @ParamConverter("request", converter="githubRequest")
     * @View
     */
    public function flowWebHookAction(FlatFlow $flow, GitHubRequest $request)
    {
        $this->commandBus->handle(new HandleGitHubEvent(
            $flow->getUuid(),
            $request->getEvent()
        ));
    }

    /**
     * @Route("/github/integration/webhook", methods={"POST"}, name="github_integration_webhook")
     * @ParamConverter("request", converter="githubRequest")
     * @View
     */
    public function integrationAction(GitHubRequest $request)
    {
        $event = $request->getEvent();
        if ($event instanceof IntegrationInstallationEvent) {
            if ($event->isDeletedAction()) {
                $this->eventBus->handle(new IntegrationInstallationDeleted($event->getInstallation()));
            }
            return new Response(null, Response::HTTP_ACCEPTED);
        }

        $repository = GitHubCodeRepository::fromRepository($event->getRepository());
        $flows = $this->flowRepository->findByCodeRepository($repository);

        if (empty($flows)) {
            $this->logger->warning('No flows found for this repository', [
                'repository_type' => $repository->getType(),
                'repository_identifier' => $repository->getIdentifier(),
            ]);

            return new JsonResponse(['message' => 'No matching flows found for this repository'], 404);
        }

        foreach ($flows as $flow) {
            $this->commandBus->handle(new HandleGitHubEvent(
                $flow->getUuid(),
                $event
            ));
        }

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
