<?php

namespace ContinuousPipe\Authenticator\Tests;

use ContinuousPipe\Authenticator\WhiteList\WhiteList;

class InMemoryWhiteList implements WhiteList
{
    /**
     * @var array
     */
    private $userNames = [];

    /**
     * {@inheritdoc}
     */
    public function contains($username)
    {
        return in_array($username, $this->userNames);
    }

    /**
     * {@inheritdoc}
     */
    public function add($username)
    {
        $this->userNames[] = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($username)
    {
        if ($this->contains($username)) {
            $this->userNames = array_filter($this->userNames, function ($value) use ($username) {
                return $username != $value;
            });
        }
    }
}
