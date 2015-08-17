<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.user_organizations")
 */
class UserOrganizationsController
{
    /**
     * @var CodeRepositoryRepository
     */
    private $organizationRepository;

    /**
     * @param CodeRepositoryRepository $organizationRepository
     */
    public function __construct($organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @Route("/user-organizations")
     * @View
     */
    public function listAction()
    {
        return $this->organizationRepository->findByCurrentUser();
    }
}
