<?php

namespace ContinuousPipe\CloudFlare;

use ContinuousPipe\Model\Component\Endpoint\CloudFlareAuthentication;

class CallbackClient implements CloudFlareClient
{
    private $deleteCallback;

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateRecord(string $zone, CloudFlareAuthentication $authentication, ZoneRecord $record) : string
    {
        return '1234';
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecord(string $zone, CloudFlareAuthentication $authentication, string $recordIdentifier)
    {
        if (null === $this->deleteCallback) {
            return;
        }

        $callback = $this->deleteCallback;
        $callback($zone, $authentication, $recordIdentifier);
    }

    public function setDeleteCallback($deleteCallback)
    {
        $this->deleteCallback = $deleteCallback;
    }
}
