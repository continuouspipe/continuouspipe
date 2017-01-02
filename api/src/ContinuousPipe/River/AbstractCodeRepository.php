<?php

namespace ContinuousPipe\River;

use JMS\Serializer\Annotation as JMS;

/**
 * The purpose of this abstract class is only to be able to use the map configuration
 * of JMS serializer and Doctrine.
 *
 * @JMS\Discriminator(field="type", map={
 *    "github": "ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository",
 *    "bitbucket": "ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository",
 * })
 */
abstract class AbstractCodeRepository implements CodeRepository
{
    /**
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getIdentifier")
     *
     * @var string
     */
    protected $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
