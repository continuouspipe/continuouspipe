<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket;

use ContinuousPipe\River\CodeRepository\Organisation;

class BitBucketOrganisation implements Organisation
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string|null
     */
    private $avatarUrl;

    public function __construct(string $identifier, string $avatarUrl = null)
    {
        $this->identifier = $identifier;
        $this->avatarUrl = $avatarUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
