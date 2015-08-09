<?php

namespace AppBundle\Controller;

use ContinuousPipe\Adapter\AdapterRegistry;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="pipe.controllers.create_provider")
 */
class CreateProviderController extends Controller
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var AdapterRegistry
     */
    private $adapterRegistry;

    /**
     * @param AdapterRegistry $adapterRegistry
     * @param Serializer      $serializer
     */
    public function __construct(AdapterRegistry $adapterRegistry, Serializer $serializer)
    {
        $this->serializer = $serializer;
        $this->adapterRegistry = $adapterRegistry;
    }

    /**
     * @Route("/providers/{type}", methods={"POST"})
     * @View
     */
    public function createAction(Request $request, $type)
    {
        $adapter = $this->adapterRegistry->getByType($type);
        $className = $adapter->getConfigurationClass();
        $configuration = $this->serializer->deserialize($request->getContent(), $className, 'json');

        return $adapter->getRepository()->create($configuration);
    }
}
