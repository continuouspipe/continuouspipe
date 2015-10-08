<?php

namespace UserApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\DockerRegistryCredentials;
use ContinuousPipe\User\User;

/**
 * @Route(service="user_api.controller.docker_registry_credentials")
 */
class DockerRegistryCredentialsController
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
     * @Route("/docker-registries", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("credentials", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(User $user, DockerRegistryCredentials $credentials)
    {
        $credentials = $this->dockerRegistryCredentialsRepository->save($credentials, $user);

        return $credentials;
    }
}
