<?php

namespace ContinuousPipe\Security\Team;

use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class TeamMembershipCollection extends ArrayCollection
{
    public function __construct($elements = [])
    {
        if ($elements instanceof Collection) {
            $elements = $elements->toArray();
        }

        parent::__construct($elements);
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function admins()
    {
        return $this->filter(function (TeamMembership $membership) {
            return $membership->isAdmin();
        })->map(function (TeamMembership $membership) {
            return $membership->getUser();
        });
    }
}
