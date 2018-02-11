<?php

namespace AppBundle\Controller;

use ContinuousPipe\Managed\DockerRegistry\DockerRegistryManager;
use ContinuousPipe\Managed\DockerRegistry\DockerRegistryManagerResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use ContinuousPipe\Security\Credentials\BucketRepository;
use ContinuousPipe\Security\Credentials\DockerRegistry;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route(service="app.controller.resources")
 */
class FlowResourcesController
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var DockerRegistryManagerResolver
     */
    private $managerResolver;

    /**
     * @var string
     */
    private $managedRegistryDsn;

    public function __construct(
        DockerRegistryManagerResolver $managerResolver,
        BucketRepository $bucketRepository,
        string $managedRegistryDsn
    ) {
        $this->bucketRepository = $bucketRepository;
        $this->managerResolver = $managerResolver;
        $this->managedRegistryDsn = $managedRegistryDsn;
    }

    /**
     * @Route("/flows/{uuid}/resources/registry", methods={"POST"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('UPDATE', flow)")
     * @View(statusCode=201)
     */
    public function createRegistryAction(FlatFlow $flow, Request $request)
    {
        if (!empty($contents = $request->getContent())) {
            $json = \GuzzleHttp\json_decode($contents, true);

            if (!isset($json['visibility'])) {
                throw new BadRequestHttpException('`visibility` field is required');
            }

            $visibility = $json['visibility'];
        } else {
            $visibility = 'private';
        }

        return $this->managerResolver->get($this->managedRegistryDsn)->createRepositoryForFlow($flow, $visibility);
    }

    /**
     * @Route("/flows/{uuid}/resources/registry/{registryAddress}/visibility", methods={"POST"}, requirements={"registryAddress"=".+"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('UPDATE', flow)")
     * @View
     */
    public function changeRegistryVisibilityAction(FlatFlow $flow, string $registryAddress, Request $request)
    {
        $registry = $this->registryFromAddress($flow->getTeam()->getBucketUuid(), $registryAddress);
        if (!isset($registry->getAttributes()['flow'])) {
            throw new BadRequestHttpException('Registry is not linked to any flow');
        } elseif ($registry->getAttributes()['flow'] != $flow->getUuid()->toString()) {
            throw new BadRequestHttpException('Registry is not linked to this flow');
        }

        try {
            $requestContents = \GuzzleHttp\json_decode($request->getContent(), true);

            if (!isset($requestContents['visibility'])) {
                throw new \InvalidArgumentException('`visibiliy` is required');
            }
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e);
        }

        $this->managerResolver->get($this->managedRegistryDsn)->changeVisibility($flow, $registry, $requestContents['visibility']);
    }

    /**
     * @param UuidInterface $bucketUuid
     * @param string $registryAddress
     *
     * @return DockerRegistry
     */
    private function registryFromAddress(UuidInterface $bucketUuid, string $registryAddress)
    {
        $bucket = $this->bucketRepository->find($bucketUuid);

        foreach ($bucket->getDockerRegistries() as $registry) {
            if ($registry->getFullAddress() == $registryAddress) {
                return $registry;
            }
        }

        throw new NotFoundHttpException('Registry was not found');
    }
}
