<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenResolver;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.github_installation_token")
 */
class GitHubInstallationTokenController
{
    /**
     * @var InstallationTokenResolver
     */
    private $installationTokenResolver;

    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    public function __construct(
        InstallationTokenResolver $installationTokenResolver,
        InstallationRepository $installationRepository
    ) {
        $this->installationTokenResolver = $installationTokenResolver;
        $this->installationRepository = $installationRepository;
    }

    /**
     * @Route("/github/flows/{flowUuid}/installation-token", methods={"GET"}, name="github_flow_installation_token")
     * @ParamConverter("flow", converter="flow", options={"identifier"="flowUuid", "flat"=true})
     * @View
     */
    public function getByFlow(FlatFlow $flow)
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            throw new BadRequestHttpException('The repository for this flow is not supported');
        }

        return $this->installationTokenResolver->get(
            $this->installationRepository->findByRepository($repository)
        );
    }
}
