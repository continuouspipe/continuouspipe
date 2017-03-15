<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\AtlassianAddon\BitBucket\Repository;
use ContinuousPipe\River\AbstractCodeRepository;
use JMS\Serializer\Annotation as JMS;

class BitBucketCodeRepository extends AbstractCodeRepository
{
    /**
     * @JMS\Type("ContinuousPipe\River\CodeRepository\BitBucket\BitBucketAccount")
     *
     * @var BitBucketAccount
     */
    private $owner;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $address;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $defaultBranch;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $private;

    public function __construct(string $identifier, BitBucketAccount $owner, string $name, string $address, string $defaultBranch, bool $private)
    {
        parent::__construct($identifier);

        $this->name = $name;
        $this->owner = $owner;
        $this->address = $address;
        $this->defaultBranch = $defaultBranch;
        $this->private = $private;
    }

    public static function fromBitBucketRepository(Repository $repository) : BitBucketCodeRepository
    {
        return new self(
            $repository->getUuid(),
            BitBucketAccount::fromActor($repository->getOwner()),
            $repository->getName(),
            $repository->getLinks()->getSelf()->getHref(),
            'master',
            $repository->isPrivate()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBranch()
    {
        return $this->defaultBranch;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'bitbucket';
    }

    /**
     * @return BitBucketAccount
     */
    public function getOwner(): BitBucketAccount
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @return string
     */
    public function getApiSlug() : string
    {
        return $this->owner->getUsername().substr($this->address, strrpos($this->address, '/'));
    }
}
