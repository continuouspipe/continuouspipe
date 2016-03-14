<?php

namespace AppBundle\Controller\Wizard;

use ContinuousPipe\River\CodeRepository\OrganisationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/wizard", service="app.controller.wizard.organisations")
 */
class OrganisationsController
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
     * @Route("/organisations")
     * @View
     */
    public function listAction()
    {
        return $this->organisationRepository->findByCurrentUser();
    }
}
