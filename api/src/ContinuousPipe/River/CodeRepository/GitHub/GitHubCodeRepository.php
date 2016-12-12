<?php

namespace ContinuousPipe\River\CodeRepository\GitHub;

use ContinuousPipe\River\AbstractCodeRepository;
use GitHub\WebHook\Model\Repository;
use JMS\Serializer\Annotation as JMS;

class GitHubCodeRepository extends AbstractCodeRepository
{
    /**
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getAddress")
     *
     * @var string
     */
    private $address;

    /**
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getOrganisation")
     *
     * @var string
     */
    private $organisation;

    /**
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getName")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("boolean")
     * @JMS\Accessor(getter="isPrivate")
     *
     * @var bool
     */
    private $private = false;

    /**
     * @deprecated This method is a BC for the previously stored (serialized) GitHubCodeRepository objects
     *
     * @JMS\Exclude
     *
     * @var Repository
     */
    private $repository;

    public function __construct(string $identifier, string $address, string $organisation, string $name, bool $private)
    {
        $this->identifier = $identifier;
        $this->address = $address;
        $this->organisation = $organisation;
        $this->name = $name;
        $this->private = $private;
    }

    /**
     * @param Repository $repository
     *
     * @return GitHubCodeRepository
     */
    public static function fromRepository(Repository $repository)
    {
        return new self(
            $repository->getId(),
            $repository->getUrl(),
            $repository->getOwner()->getLogin(),
            $repository->getName(),
            $repository->isPrivate()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        if ($this->repository) {
            $this->populateFieldsFromRepository($this->repository);
        }

        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        if ($this->repository) {
            $this->populateFieldsFromRepository($this->repository);
        }

        return $this->address;
    }

    public function getOrganisation() : string
    {
        if ($this->repository) {
            $this->populateFieldsFromRepository($this->repository);
        }

        return $this->organisation;
    }

    public function getName() : string
    {
        if ($this->repository) {
            $this->populateFieldsFromRepository($this->repository);
        }

        return $this->name;
    }

    public function isPrivate() : bool
    {
        if ($this->repository) {
            $this->populateFieldsFromRepository($this->repository);
        }

        return $this->private;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'github';
    }

    private function populateFieldsFromRepository(Repository $repository)
    {
        $this->identifier = $repository->getId();
        $this->address = $repository->getUrl();
        $this->organisation = $repository->getOwner()->getLogin();
        $this->name = $repository->getName();
        $this->private = (bool) $repository->isPrivate();
    }
}
