<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.user_organisations")
 */
class UserOrganisationsController
{
    /**
     * @var CodeRepositoryRepository
     */
    private $organisationRepository;

    /**
     * @param CodeRepositoryRepository $organisationRepository
     */
    public function __construct($organisationRepository)
    {
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * @Route("/user-organisations")
     * @View
     */
    public function listAction()
    {
        return $this->organisationRepository->findByCurrentUser();
    }
}
