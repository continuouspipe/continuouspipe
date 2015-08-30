<?php

namespace ContinuousPipe\River\CodeRepository;

interface Organisation
{
    /**
     * Get organisation identifier.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getReposUrl();
}
