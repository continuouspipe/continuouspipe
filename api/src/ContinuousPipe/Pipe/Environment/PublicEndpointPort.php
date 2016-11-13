<?php

namespace ContinuousPipe\Pipe\Environment;

class PublicEndpointPort
{
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';

    /**
     * @var int
     */
    private $number;

    /**
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
