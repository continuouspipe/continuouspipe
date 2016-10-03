<?php

namespace GitHub\Integration;

interface InstallationRepository
{
    /**
     * @return Installation[]
     */
    public function findAll();

    /**
     * @param string $account
     *
     * @throws InstallationNotFound
     *
     * @return Installation
     */
    public function findByAccount($account);
}
