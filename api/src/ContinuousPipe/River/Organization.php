<?php

namespace ContinuousPipe\River;

interface Organization
{
    /**
     * Get organization identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getReposUrl();
}
