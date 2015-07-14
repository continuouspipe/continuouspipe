<?php

namespace AppBundle\Controller;

use AppBundle\Repository\UserRepositoryRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="app.controller.user_repositories")
 */
class UserRepositoriesController
{
    /**
     * @var UserRepositoryRepository
     */
    private $userRepositoryRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(UserRepositoryRepository $userRepositoryRepository, SerializerInterface $serializer)
    {
        $this->userRepositoryRepository = $userRepositoryRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user-repositories")
     */
    public function listAction()
    {
        $repositories = $this->userRepositoryRepository->findByCurrentUser();

        return new Response($this->serializer->serialize($repositories, 'json'), 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
