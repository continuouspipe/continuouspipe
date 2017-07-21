<?php

namespace GitHub\WebHook\Event;

use ContinuousPipe\River\CodeRepository\CodeRepositoryNotFound;
use GitHub\Integration\Installation;
use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

abstract class InstallationEvent extends AbstractEvent
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $action;

    /**
     * @var Installation
     *
     * @JMS\Type("GitHub\Integration\Installation")
     */
    private $installation;

    /**
     * @return Installation
     */
    public function getInstallation(): Installation
    {
        return $this->installation;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Returns the repository related to this event.
     *
     * @return Repository
     *
     * @throws CodeRepositoryNotFound
     */
    public function getRepository()
    {
        throw new CodeRepositoryNotFound('Installation event is not connected to a specific repository.');
    }
}
