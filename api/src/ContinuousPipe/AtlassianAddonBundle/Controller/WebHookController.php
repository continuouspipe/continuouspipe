<?php

namespace ContinuousPipe\AtlassianAddonBundle\Controller;

use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\WebHookEvent;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\Command\HandleBitBucketEvent;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="atlassian_addon.controllers.web_hook")
 */
class WebHookController
{
    private $flowRepository;
    private $commandBus;
    private $logger;

    public function __construct(FlatFlowRepository $flowRepository, MessageBus $commandBus, LoggerInterface $logger)
    {
        $this->flowRepository = $flowRepository;
        $this->commandBus = $commandBus;
        $this->logger = $logger;
    }

    /**
     * @Route("/webhook", methods={"POST"})
     * @ParamConverter("event", converter="bitbucket_webhook")
     * @View
     */
    public function eventAction(WebHookEvent $event)
    {
        $repository = BitBucketCodeRepository::fromBitBucketRepository($event->getRepository());
        $flows = $this->flowRepository->findByCodeRepository($repository);

        if (empty($flows)) {
            $this->logger->warning('No flows found for this repository', [
                'repository_type' => $repository->getType(),
                'repository_identifier' => $repository->getIdentifier(),
            ]);

            return new JsonResponse(['message' => 'No matching flows found for this repository'], 404);
        }

        foreach ($flows as $flow) {
            $this->commandBus->handle(new HandleBitBucketEvent(
                $flow->getUuid(),
                $event
            ));
        }

        return new Response(null, Response::HTTP_ACCEPTED);
    }
}
