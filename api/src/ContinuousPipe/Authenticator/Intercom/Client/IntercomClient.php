<?php

namespace ContinuousPipe\Authenticator\Intercom\Client;

interface IntercomClient
{
    /**
     * @param array $lead
     *
     * @return array
     */
    public function createLead(array $lead);

    public function message(array $message);
}
