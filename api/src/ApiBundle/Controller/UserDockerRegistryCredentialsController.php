<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\SecurityUser;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="api.controller.user_docker_registry_credentials")
 */
class UserDockerRegistryCredentialsController
{
    /**
     * @var DockerRegistryCredentialsRepository
     */
    private $dockerRegistryCredentialsRepository;

    /**
     * @param DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository
     */
    public function __construct(DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository)
    {
        $this->dockerRegistryCredentialsRepository = $dockerRegistryCredentialsRepository;
    }

    /**
     * @Route("/user/{email}/credentials/docker-registry/{server}", methods={"GET"})
     * @ParamConverter("securityUser", converter="security_user")
     * @View
     */
    public function getValidAction(SecurityUser $securityUser, $server)
    {
        $credentials = $this->dockerRegistryCredentialsRepository->findByUserAndServer($securityUser->getUser(), $server);

        return $credentials;
    }
}
