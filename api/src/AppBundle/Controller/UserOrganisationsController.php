<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\OrganisationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.user_organisations")
 */
class UserOrganisationsController
{
    /**
     * @var OrganisationRepository
     */
    private $organisationRepository;

    /**
     * @param OrganisationRepository $organisationRepository
     */
    public function __construct(OrganisationRepository $organisationRepository)
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
