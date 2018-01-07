<?php

namespace GitHub\WebHook\Event;

use ContinuousPipe\River\CodeRepository\CodeRepositoryNotFound;
use GitHub\Integration\Installation;
use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class IntegrationInstallationEvent extends InstallationEvent
{
    public function isCreatedAction(): bool
    {
        return $this->getAction() === 'created';
    }

    public function isDeletedAction(): bool
    {
        return $this->getAction() === 'deleted';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'integration_installation';
    }
}
