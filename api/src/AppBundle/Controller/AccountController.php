<?php

namespace AppBundle\Controller;

use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route(service="app.controller.account")
 */
class AccountController
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
     * @Route("/account")
     * @ParamConverter("user", converter="user")
     * @Template
     */
    public function overviewAction(User $user)
    {
        return [
            'dockerRegistryCredentials' => $this->dockerRegistryCredentialsRepository->findByUser($user),
            'user' => $user,
        ];
    }
}
