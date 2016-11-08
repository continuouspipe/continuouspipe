<?php

namespace ContinuousPipe\River\Alerts\OnRuntime\GitHub;

use ContinuousPipe\River\Alerts\Alert;
use ContinuousPipe\River\Alerts\AlertAction;
use ContinuousPipe\River\Alerts\AlertsRepository;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\View\Flow;
use ContinuousPipe\River\GitHub\ClientFactory;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GuzzleHttp\Exception\RequestException;

class FlowInstallationNotFound implements AlertsRepository
{
    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @param InstallationRepository $installationRepository
     * @param ClientFactory          $clientFactory
     */
    public function __construct(InstallationRepository $installationRepository, ClientFactory $clientFactory)
    {
        $this->installationRepository = $installationRepository;
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(Flow $flow)
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            return [];
        }

        $alerts = [];
        try {
            $gitHubRepository = $repository->getGitHubRepository();
            $installation = $this->installationRepository->findByAccount(
                $gitHubRepository->getOwner()->getLogin()
            );

            $client = $this->clientFactory->createClientFromInstallation($installation);

            try {
                $client->repo()->show(
                    $gitHubRepository->getOwner()->getLogin(), $gitHubRepository->getName()
                );
            } catch (RequestException $e) {
                $alerts[] = new Alert(
                    'github-integration-no-access',
                    'The GitHub integration do not have access to this repository',
                    new \DateTime(),
                    new AlertAction('link', 'Configure', 'https://github.com/integration/continuouspipe')
                );
            }
        } catch (InstallationNotFound $e) {
            $alerts[] = new Alert(
                'github-integration-not-found',
                'The GitHub integration is not installed',
                new \DateTime(),
                new AlertAction('link', 'Install', 'https://github.com/integration/continuouspipe')
            );
        }

        return $alerts;
    }
}
