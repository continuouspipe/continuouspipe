<?php

namespace ContinuousPipe\River\Silent\CodeRepository;

use ContinuousPipe\River\CodeRepository\GitHub\PullRequestDeploymentNotifier;
use ContinuousPipe\River\Task\Deploy\Event\DeploymentSuccessful;
use ContinuousPipe\River\View\TideRepository;
use GitHub\WebHook\Model\PullRequest;
use GitHub\WebHook\Model\Repository;
use Ramsey\Uuid\Uuid;

class SilentDeploymentNotifier implements PullRequestDeploymentNotifier
{
    /**
     * @var PullRequestDeploymentNotifier
     */
    private $decoratedNotifier;

    /**
     * @var TideRepository
     */
    private $tideRepository;

    /**
     * @param PullRequestDeploymentNotifier $decoratedNotifier
     * @param TideRepository                $tideRepository
     */
    public function __construct(PullRequestDeploymentNotifier $decoratedNotifier, TideRepository $tideRepository)
    {
        $this->decoratedNotifier = $decoratedNotifier;
        $this->tideRepository = $tideRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(DeploymentSuccessful $deploymentSuccessful, Repository $repository, PullRequest $pullRequest)
    {
        if ($this->tideIsSilent($deploymentSuccessful->getTideUuid())) {
            return;
        }

        $this->decoratedNotifier->notify($deploymentSuccessful, $repository, $pullRequest);
    }

    /**
     * Returns true if the tide is silent.
     *
     * @param Uuid $tideUuid
     *
     * @return bool
     */
    private function tideIsSilent(Uuid $tideUuid)
    {
        $tide = $this->tideRepository->find($tideUuid);
        $configuration = $tide->getConfiguration();

        return array_key_exists('silent', $configuration) && $configuration['silent'];
    }
}
