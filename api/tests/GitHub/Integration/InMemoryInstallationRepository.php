<?php

namespace GitHub\Integration;

class InMemoryInstallationRepository implements InstallationRepository
{
    /**
     * @var Installation[]
     */
    private $installations = [];

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->installations;
    }

    /**
     * {@inheritdoc}
     */
    public function findByAccount($account)
    {
        $matchingInstallations = array_filter($this->installations, function(Installation $installation) use ($account) {
            return $installation->getAccount()->getLogin() == $account;
        });

        if (count($matchingInstallations) == 0) {
            throw new InstallationNotFound();
        }

        return current($matchingInstallations);
    }
}
