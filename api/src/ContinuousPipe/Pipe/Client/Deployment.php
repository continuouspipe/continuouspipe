<?php

namespace ContinuousPipe\Pipe\Client;

/**
 * @deprecated Duplicate of the `ContinuousPipe\Pipe\View\Deployment` object, after merging pipe. Kept to be compatible
 *             with serialized tides.
 */
class Deployment extends \ContinuousPipe\Pipe\View\Deployment
{
    private $status;
    private $request;
    private $user;
    private $publicEndpoints;
    private $componentStatuses;

    public function getStatus()
    {
        return $this->status ?? parent::getStatus();
    }

    public function getRequest()
    {
        return $this->request ?? parent::getRequest();
    }

    public function getUser()
    {
        return $this->user ?? parent::getUser();
    }

    public function getPublicEndpoints()
    {
        return $this->publicEndpoints ?? parent::getPublicEndpoints();
    }

    public function getComponentStatuses()
    {
        return $this->componentStatuses ?? parent::getComponentStatuses();
    }
}
