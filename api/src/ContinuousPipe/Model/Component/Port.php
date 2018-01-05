<?php

namespace ContinuousPipe\Model\Component;

class Port
{
    const PROTOCOL_TCP = 'TCP';
    const PROTOCOL_UDP = 'UDP';

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     * @param int    $port
     * @param string $protocol
     */
    public function __construct($identifier, $port, $protocol = self::PROTOCOL_TCP)
    {
        $this->port = (int) $port;
        $this->protocol = $protocol;
        $this->identifier = $identifier;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
