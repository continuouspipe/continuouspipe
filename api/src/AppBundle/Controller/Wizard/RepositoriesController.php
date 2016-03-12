<?php

namespace AppBundle\Controller\Wizard;

use ContinuousPipe\River\Repository\CodeRepositoryRepository;
use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/wizard", service="app.controller.wizard.repositories")
 */
class RepositoriesController
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
     * @Route("/repositories")
     * @ParamConverter("user", converter="user")
     * @View
     */
    public function listAction(User $user)
    {
        return $this->codeRepositoryRepository->findByUser($user);
    }

    /**
     * @Route("/organisations/{organisation}/repositories")
     * @View
     */
    public function listByOrganisationAction($organisation)
    {
        return $this->codeRepositoryRepository->findByOrganisation($organisation);
    }
}
