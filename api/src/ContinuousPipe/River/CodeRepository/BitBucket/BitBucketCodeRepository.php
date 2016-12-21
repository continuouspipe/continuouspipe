<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\AbstractCodeRepository;

class BitBucketCodeRepository extends AbstractCodeRepository
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $defaultBranch;

    /**
     * @var bool
     */
    private $private;

    public function __construct(string $identifier, string $name, string $address, string $defaultBranch, bool $private)
    {
        parent::__construct($identifier);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->address = $address;
        $this->defaultBranch = $defaultBranch;
        $this->private = $private;
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
}