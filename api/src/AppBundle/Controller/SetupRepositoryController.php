<?php

namespace AppBundle\Controller;

use AppBundle\Repository\UserRepositoryRepository;
use GitHub\WebHook\Setup\WebHookManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.setup_repository")
 */
class SetupRepositoryController
{
    /**
     * @var WebHookManager
     */
    private $webHookManager;
    /**
     * @var UserRepositoryRepository
     */
    private $userRepositoryRepository;

    /**
     * @param WebHookManager $webHookManager
     */
    public function __construct(WebHookManager $webHookManager, UserRepositoryRepository $userRepositoryRepository)
    {
        $this->webHookManager = $webHookManager;
        $this->userRepositoryRepository = $userRepositoryRepository;
    }

    /**
     * @Route("/user-repositories/{id}/activate", methods={"POST"})
     */
    public function setupAction($id)
    {
        $repository = $this->userRepositoryRepository->findById($id);

        $this->webHookManager->setup($repository);

        return new Response('OK');
    }
}
