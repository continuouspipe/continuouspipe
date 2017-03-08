<?php

namespace ContinuousPipe\AtlassianAddon;

class InMemoryInstallationRepository implements InstallationRepository
{
    /**
     * @var Installation[]
     */
    private $installations = [];

    public function save(Installation $installation)
    {
        $this->installations[] = $installation;
    }

    public function findByPrincipal(string $type, string $username): array
    {
        return array_values(array_filter($this->installations, function(Installation $installation) use ($type, $username) {
            $principal = $installation->getPrincipal();

            return $principal->getType() == $type && $principal->getUsername() == $username;
        }));
    }

    public function remove(Installation $installation)
    {
        foreach ($this->installations as $key => $foundInstallation) {
            if ($installation->getClientKey() == $foundInstallation->getClientKey()) {
                unset($this->installations[$key]);
            }
        }
    }
}
