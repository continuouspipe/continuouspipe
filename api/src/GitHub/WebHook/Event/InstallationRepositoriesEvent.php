<?php

namespace GitHub\WebHook\Event;

use ContinuousPipe\River\CodeRepository\CodeRepositoryNotFound;
use GitHub\Integration\Installation;
use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class InstallationRepositoriesEvent extends InstallationEvent
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'installation_repositories';
    }
}
