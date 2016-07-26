<?php

namespace ContinuousPipe\Authenticator\Tests\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;

class TraceableIntercomClient implements IntercomClient
{
    /**
     * @var IntercomClient
     */
    private $decoratedClient;

    /**
     * @var array[]
     */
    private $createdLeads = [];

    /**
     * @param IntercomClient $decoratedClient
     */
    public function __construct(IntercomClient $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function createLead(array $lead)
    {
        $this->createdLeads[] = $lead;

        $this->decoratedClient->createLead($lead);
    }

    /**
     * @return array[]
     */
    public function getCreatedLeads()
    {
        return $this->createdLeads;
    }
}
