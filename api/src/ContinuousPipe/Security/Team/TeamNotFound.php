<?php

namespace ContinuousPipe\Security\Team;

class TeamNotFound extends \Exception
{
    public static function createFromSlug($slug)
    {
        return new self(sprintf(
            'Team "%s" not found',
            $slug
        ));
    }
}
