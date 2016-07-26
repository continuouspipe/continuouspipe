<?php

namespace ContinuousPipe\Authenticator\Intercom\Client;

interface IntercomClient
{
    /**
     * @param array $lead
     *
     * @return \stdClass
     */
    public function createLead(array $lead);

    public function message(array $message);
}
