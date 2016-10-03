<?php

namespace GitHub\Integration;

use JMS\Serializer\Annotation as JMS;

class InstallationToken
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $token;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTimeInterface
     */
    private $expiresAt;

    /**
     * @JMS\Type("string")
     *
     * @var null|string
     */
    private $onBehalfOf;

    /**
     * @param string $token
     * @param \DateTimeInterface $expiresAt
     * @param string|null $onBehalfOf
     */
    public function __construct($token, \DateTimeInterface $expiresAt, $onBehalfOf = null)
    {
        $this->token = $token;
        $this->expiresAt = $expiresAt;
        $this->onBehalfOf = $onBehalfOf;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return null|string
     */
    public function getOnBehalfOf()
    {
        return $this->onBehalfOf;
    }
}
