<?php

namespace ContinuousPipe\River\CodeRepository;

interface OrganisationRepository
{
    /**
     * @deprecated Please use the code repository explorer instead.
     *
     * @return Organisation[]
     */
    public function findByCurrentUser();
}
