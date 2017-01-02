<?php

namespace ContinuousPipe\AtlassianAddonBundle\Controller;

use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\InstallationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="atlassian_addon.controllers.installation")
 */
class InstallationController
{
    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @param InstallationRepository $installationRepository
     */
    public function __construct(InstallationRepository $installationRepository)
    {
        $this->installationRepository = $installationRepository;
    }

    /**
     * @Route("/installed", methods={"POST"})
     * @ParamConverter("installation", converter="fos_rest.request_body")
     * @View
     */
    public function installedAction(Installation $installation)
    {
        $this->installationRepository->save($installation);
    }
}
