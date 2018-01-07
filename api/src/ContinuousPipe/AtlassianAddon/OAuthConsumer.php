<?php

namespace ContinuousPipe\AtlassianAddon;

use JMS\Serializer\Annotation as JMS;

class OAuthConsumer
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $secret;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $key;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
