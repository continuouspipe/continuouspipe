<?php

namespace ContinuousPipe\UserDetails;

interface UserDetails
{
    /**
     * Using the access token, retrieve the email address for a user
     *
     * @param string $accessToken Access token
     * @return string Email address
     */
    public function getEmailAddress($accessToken);
}
