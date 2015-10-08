<?php

namespace UserApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\DockerRegistryCredentials;
use ContinuousPipe\User\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\View\View as FOSRestView;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository
     * @param ValidatorInterface                  $validator
     */
    public function __construct(DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository, ValidatorInterface $validator)
    {
        $this->dockerRegistryCredentialsRepository = $dockerRegistryCredentialsRepository;
        $this->validator = $validator;
    }

    /**
     * @Route("/docker-registries", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @ParamConverter("credentials", converter="fos_rest.request_body")
     * @View(statusCode=201)
     */
    public function createAction(User $user, DockerRegistryCredentials $credentials)
    {
        $violations = $this->validator->validate($credentials);
        if (count($violations) > 0) {
            return FOSRestView::create($violations, 400);
        }

        $credentials = $this->dockerRegistryCredentialsRepository->save($credentials, $user);

        return $credentials;
    }
}
