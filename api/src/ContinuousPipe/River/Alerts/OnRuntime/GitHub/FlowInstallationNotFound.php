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
            $this->installationRepository->findByRepository($repository);
        } catch (InstallationNotFound $e) {
            $alerts[] = new Alert(
                'github-integration',
                $e->getMessage(),
                new \DateTime(),
                new AlertAction('link', 'Install', 'https://github.com/integration/continuouspipe')
            );
        }

        return $alerts;
    }
}
