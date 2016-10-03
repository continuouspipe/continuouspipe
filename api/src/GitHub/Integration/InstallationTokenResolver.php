<?php

namespace GitHub\Integration;

interface InstallationTokenResolver
{
    /**
     * @param Installation $installation
     *
     * @return InstallationToken
     */
    public function get(Installation $installation);
}
