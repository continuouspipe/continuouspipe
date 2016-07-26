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

    /**
     * @param array $message
     *
     * @return array
     */
    public function message(array $message);

    /**
     * @param array $user
     *
     * @return array
     */
    public function createOrUpdateUser(array $user);
}
