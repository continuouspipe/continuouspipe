<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\DockerRegistryCredentialsFormType;
use ContinuousPipe\Authenticator\DockerRegistryCredentialsRepository;
use ContinuousPipe\User\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route(service="app.controller.docker_registry_credentials")
 */
class DockerRegistryCredentialsController
{
    /**
     * @var DockerRegistryCredentialsRepository
     */
    private $dockerRegistryCredentialsRepository;
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository
     * @param FormFactoryInterface                $formFactory
     * @param UrlGeneratorInterface               $urlGenerator
     */
    public function __construct(DockerRegistryCredentialsRepository $dockerRegistryCredentialsRepository, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator)
    {
        $this->dockerRegistryCredentialsRepository = $dockerRegistryCredentialsRepository;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/account/credentials/docker-registry/add", name="credentials_docker_registry_add")
     * @ParamConverter("user", converter="user")
     * @Template
     */
    public function addAction(Request $request, User $user)
    {
        $form = $this->formFactory->create(new DockerRegistryCredentialsFormType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $registry = $form->getData();

            $this->dockerRegistryCredentialsRepository->save($registry, $user);

            return new RedirectResponse($this->urlGenerator->generate('credentials_docker_registry', [
                'serverAddress' => $registry->getServerAddress(),
            ]));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/account/credentials/docker-registry/{serverAddress}", name="credentials_docker_registry")
     * @ParamConverter("user", converter="user")
     * @Template
     */
    public function editAction(Request $request, User $user, $serverAddress)
    {
        $dockerRegistryCredentials = $this->dockerRegistryCredentialsRepository->findOneByUserAndServer($user, $serverAddress);
        $form = $this->formFactory->create(new DockerRegistryCredentialsFormType(), $dockerRegistryCredentials);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $registry = $form->getData();

            $this->dockerRegistryCredentialsRepository->save($registry, $user);

            return new RedirectResponse($this->urlGenerator->generate('credentials_docker_registry', [
                'serverAddress' => $registry->getServerAddress(),
            ]));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
