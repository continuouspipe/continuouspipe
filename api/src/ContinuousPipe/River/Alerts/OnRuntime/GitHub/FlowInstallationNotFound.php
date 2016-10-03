<?php

namespace ContinuousPipe\River\Alerts\OnRuntime\GitHub;

use ContinuousPipe\River\Alerts\Alert;
use ContinuousPipe\River\Alerts\AlertAction;
use ContinuousPipe\River\Alerts\AlertsRepository;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;

class FlowInstallationNotFound implements AlertsRepository
{
    /**
     * @var InstallationRepository
     */
    private $installationRepository;

    /**
     * @param InstallationRepository $installationRepository
     */
    public function __construct(InstallationRepository $installationRepository)
    {
        $this->installationRepository = $installationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(Flow $flow)
    {
        $repository = $flow->getContext()->getCodeRepository();
        if (!$repository instanceof GitHubCodeRepository) {
            return [];
        }

        $alerts = [];
        try {
            $this->installationRepository->findByAccount(
                $repository->getGitHubRepository()->getOwner()->getLogin()
            );
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
