<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Alerts;

use ContinuousPipe\AtlassianAddon\InstallationRepository;
use ContinuousPipe\River\Alerts\Alert;
use ContinuousPipe\River\Alerts\AlertAction;
use ContinuousPipe\River\Alerts\AlertsRepository;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class BitBucketAddonNotFound implements AlertsRepository
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
    public function findByFlow(FlatFlow $flow)
    {
        $repository = $flow->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            return [];
        }

        $installations = $this->installationRepository->findByPrincipal(
            $repository->getOwner()->getType(),
            $repository->getOwner()->getUsername()
        );

        $alerts = [];
        if (count($installations) == 0) {
            $alerts[] = new Alert(
                'bitbucket-addon',
                'The BitBucket addon is not installed on the code repository\'s account',
                new \DateTime(),
                new AlertAction(
                    'link',
                    'Documentation',
                    'https://docs.continuouspipe.io/configuration/bitbucket-integration/'
                )
            );
        }

        return $alerts;
    }
}
