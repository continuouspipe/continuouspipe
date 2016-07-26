<?php

namespace ContinuousPipe\Authenticator\Tests\Intercom;

use ContinuousPipe\Authenticator\Intercom\Client\IntercomClient;

class InMemoryIntercomClient implements IntercomClient
{
    public function createLead(array $lead)
    {
        if (!array_key_exists('id', $lead)) {
            $lead['id'] = $lead['email'];
        }

        return $lead;
    }

    public function message(array $message)
    {
    }
}
