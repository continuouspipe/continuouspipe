<?php

namespace ContinuousPipe\Authenticator\WhiteList;

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

    /**
     * Add a user in the white list.
     *
     * @param string $username
     */
    public function add($username);

    /**
     * Remove a user from the white list.
     *
     * @param string $username
     */
    public function remove($username);
}
