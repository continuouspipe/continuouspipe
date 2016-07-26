<?php

namespace ContinuousPipe\Authenticator\Tests\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;

class InMemoryIntercomClient implements IntercomClient
{
    public function createLead(array $lead)
    {
    }
}
