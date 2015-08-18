<?php

namespace ApiBundle\Controller;

use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("/user/{email}/credentials/docker-registries", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"byEmail"="email"})
     * @View
     */
    public function listAction(User $user)
    {
        return $this->dockerRegistryCredentialsRepository->findByUser($user);
    }

    /**
     * @Route("/user/{email}/credentials/docker-registry/{server}", methods={"GET"})
     * @ParamConverter("user", converter="user", options={"byEmail"="email"})
     * @View
     */
    public function getValidAction(User $user, $server)
    {
        $credentials = $this->dockerRegistryCredentialsRepository->findOneByUserAndServer($user, $server);

        return $credentials;
    }
}
