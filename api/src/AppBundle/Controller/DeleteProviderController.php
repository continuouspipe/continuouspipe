<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\ProviderRepository;
use ContinuousPipe\Pipe\AdapterProviderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.delete_provider")
 */
class DeleteProviderController extends Controller
{
    /**
     * @var AdapterProviderRepository
     */
    private $providerRepository;

    /**
     * @param AdapterProviderRepository $providerRepository
     */
    public function __construct(AdapterProviderRepository $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * @Route("/providers/{type}/{identifier}", methods={"DELETE"})
     * @View
     */
    public function deleteAction($type, $identifier)
    {
        $provider = $this->providerRepository->findByTypeAndIdentifier($type, $identifier);

        $this->providerRepository->remove($provider);
    }
}
