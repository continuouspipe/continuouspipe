<?php

namespace ContinuousPipe\River\CodeRepository;

interface OrganisationRepository
{
    /**
     * @return Organisation[]
     */
    public function findByCurrentUser();
}
