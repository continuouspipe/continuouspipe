<?php

namespace GitHub\WebHook\Event;

use ContinuousPipe\River\CodeRepository\CodeRepositoryNotFound;
use GitHub\Integration\Installation;
use GitHub\WebHook\AbstractEvent;
use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\User;
use JMS\Serializer\Annotation as JMS;

class IntegrationInstallationEvent extends AbstractEvent
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
     * @var User
     *
     * @JMS\Type("GitHub\WebHook\Model\User")
     */
    private $sender;

    public function isCreatedAction(): bool
    {
        return $this->action === 'created';
    }

    public function isDeletedAction(): bool
    {
        return $this->action === 'deleted';
    }

    /**
     * @return Installation
     */
    public function getInstallation(): Installation
    {
        return $this->installation;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Returns the repository related to this event.
     *
     * @return Repository
     * @throws CodeRepositoryNotFound
     */
    public function getRepository()
    {
        throw new CodeRepositoryNotFound('Installation event is not connected to a specific repository.');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'integration_installation';
    }
}
