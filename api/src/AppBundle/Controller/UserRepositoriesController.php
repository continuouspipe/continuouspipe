<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.user_repositories")
 */
class UserRepositoriesController
{
    /**
     * @var CodeRepositoryRepository
     */
    private $codeRepositoryRepository;

    /**
     * @param CodeRepositoryRepository $codeRepositoryRepository
     */
    public function __construct(CodeRepositoryRepository $codeRepositoryRepository)
    {
        $this->codeRepositoryRepository = $codeRepositoryRepository;
    }

    /**
     * @Route("/user-repositories")
     * @View
     */
    public function listAction()
    {
        return $this->codeRepositoryRepository->findByCurrentUser();
    }
}
