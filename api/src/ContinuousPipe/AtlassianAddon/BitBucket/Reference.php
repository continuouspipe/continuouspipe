<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\Discriminator(field="type", map = {
 *    "branch": "ContinuousPipe\AtlassianAddon\BitBucket\Branch",
 *    "tag": "ContinuousPipe\AtlassianAddon\BitBucket\Tag"
 * })
 */
abstract class Reference
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("ContinuousPipe\AtlassianAddon\BitBucket\Commit")
     *
     * @var Commit
     */
    private $target;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Commit
     */
    public function getTarget(): Commit
    {
        return $this->target;
    }
}
