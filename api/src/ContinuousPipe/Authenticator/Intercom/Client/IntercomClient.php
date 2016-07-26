<?php

namespace ContinuousPipe\Authenticator\Intercom\Client;

interface IntercomClient
{
    public function createLead(array $lead);

    public function message(array $message);
}
