<?php

namespace ContinuousPipe\Pipe\Client;

use JMS\Serializer\Annotation as JMS;

class PublicEndpointPort
{
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $number;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $protocol;

    /**
     * @param int    $number
     * @param string $protocol
     */
    public function __construct(int $number, string $protocol)
    {
        $this->number = $number;
        $this->protocol = $protocol;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }
}
