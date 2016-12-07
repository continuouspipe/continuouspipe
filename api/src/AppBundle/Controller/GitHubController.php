<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\Command\HandleGitHubEvent;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use GitHub\WebHook\GitHubRequest;
use Psr\Log\LoggerInterface;
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
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MessageBus      $commandBus
     * @param FlowRepository  $flowRepository
     * @param LoggerInterface $logger
     */
    public function __construct(MessageBus $commandBus, FlowRepository $flowRepository, LoggerInterface $logger)
    {
        $this->commandBus = $commandBus;
        $this->flowRepository = $flowRepository;
        $this->logger = $logger;
    }

    /**
     * @Route("/web-hook/github/{uuid}", methods={"POST"}, name="web_hook_github")
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @ParamConverter("request", converter="githubRequest")
     * @View
     */
    public function flowWebHookAction(Flow\Projections\FlatFlow $flow, GitHubRequest $request)
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
        $repository = new GitHubCodeRepository($request->getEvent()->getRepository());
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
                $request->getEvent()
            ));
        }

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
