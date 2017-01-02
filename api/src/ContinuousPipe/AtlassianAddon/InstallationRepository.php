<?php

namespace ContinuousPipe\AtlassianAddon;

interface InstallationRepository
{
    public function save(Installation $installation);

    public function findByPrincipal(string $type, string $username) : array;
}
