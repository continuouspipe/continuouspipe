<?php

namespace ContinuousPipe\AtlassianAddon\BitBucket;

use JMS\Serializer\Annotation as JMS;

class CommentContent
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $raw;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $markup;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $html;
}
