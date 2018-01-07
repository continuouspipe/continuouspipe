<?php

namespace GitHub\Integration;

interface InstallationTokenResolver
{
    /**
     * @param Installation $installation
     *
     * @throws InstallationTokenException
     *
     * @return InstallationToken
     */
    public function get(Installation $installation);
}
