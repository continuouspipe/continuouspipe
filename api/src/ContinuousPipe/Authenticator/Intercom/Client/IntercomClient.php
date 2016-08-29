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

    /**
     * @param array $event
     *
     * @return array
     */
    public function createEvent(array $event);

    /**
     * @param array $lead
     * @param array $user
     *
     * @return array
     */
    public function mergeLeadIfExists(array $lead, array $user);
}
