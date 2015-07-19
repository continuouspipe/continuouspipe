<?php

namespace ContinuousPipe\River;

interface CodeRepository
{
    /**
     * Get repository identifier.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getAddress();
}
