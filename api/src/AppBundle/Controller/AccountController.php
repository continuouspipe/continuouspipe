<?php

namespace AppBundle\Controller;

use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="app.controller.account")
 */
class AccountController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var DockerRegistryCredentialsRepository
     */
    private $dockerRegistryCredentialsRepository;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository
     */
    public function __construct(TokenStorageInterface $tokenStorage, DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->dockerRegistryCredentialsRepository = $dockerRegistryCredentialsRepository;
    }

    /**
     * @Route("/account")
     * @Template
     */
    public function overviewAction()
    {
        $securityUser = $this->tokenStorage->getToken()->getUser();
        $user = $securityUser->getUser();

        return [
            'dockerRegistryCredentials' => $this->dockerRegistryCredentialsRepository->findByUser($user),
            'user' => $user
        ];
    }
}
