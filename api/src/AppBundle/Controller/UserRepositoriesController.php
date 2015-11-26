<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @ParamConverter("user", converter="user")
     * @View
     */
    public function listAction(User $user)
    {
        return $this->codeRepositoryRepository->findByUser($user);
    }

    /**
     * @Route("/user-repositories/organisation/{organisation}")
     * @View
     */
    public function listByOrganisationAction($organisation)
    {
        return $this->codeRepositoryRepository->findByOrganisation($organisation);
    }
}
