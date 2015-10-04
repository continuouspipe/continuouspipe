<?php

namespace ContinuousPipe\User;

interface WhiteList
{
    /**
     * Check if the white list contains the given user.
     *
     * @param string $username
     *
     * @return bool
     */
    public function contains($username);
}
