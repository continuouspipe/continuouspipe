<?php

namespace ContinuousPipe\AtlassianAddon;

class TraceableInstallationRepository implements InstallationRepository
{
    /**
     * @var InstallationRepository
     */
    private $decoratedRepository;

    /**
     * @var Installation[]
     */
    private $saved = [];

    /**
     * @var Installation[]
     */
    private $removed = [];

    /**
     * @param InstallationRepository $decoratedRepository
     */
    public function __construct(InstallationRepository $decoratedRepository)
    {
        $this->decoratedRepository = $decoratedRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Installation $installation)
    {
        $this->decoratedRepository->save($installation);

        $this->saved[] = $installation;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Installation $installation)
    {
        $this->decoratedRepository->remove($installation);

        $this->removed[] = $installation;
    }

    /**
     * {@inheritdoc}
     */
    public function findByPrincipal(string $type, string $username): array
    {
        return $this->decoratedRepository->findByPrincipal($type, $username);
    }

    /**
     * @return Installation[]
     */
    public function getSaved(): array
    {
        return $this->saved;
    }

    /**
     * @return Installation[]
     */
    public function getRemoved(): array
    {
        return $this->removed;
    }
}
