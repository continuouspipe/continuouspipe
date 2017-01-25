<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose\Configuration;

/**
 * This value object represents a service port configuration
 */
final class PortIdentifier
{
    const MAX_LENGTH = 15;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $port;

    /**
     * @param string $name
     * @param integer $port
     *
     * @return PortIdentifier
     */
    public static function fromNameAndPort($name, $port)
    {
        $instance = new self();
        $instance->name = $name;
        $instance->port = $port;
        return $instance;
    }

    /**
     * Return the string representation of the identifier
     *
     * @return string
     */
    public function toString()
    {
       return (string)$this;
    }

    /**
     * Trim the service name and append the port to it.
     * If the above method exceed the max length then generate the hexadecimal CRC32 checksum for name and port
     * and prefix the checksum with a truncated name (to add some meaningful string).
     *
     * @return string
     */
    public function __toString()
    {
        $count = self::MAX_LENGTH - mb_strlen($this->port);
        if ($count > 0) {
            $id = substr($this->name, 0, $count) . $this->port;
        } else {
            $crc = dechex(crc32($this->name . $this->port));
            $id = substr($this->name, 0, self::MAX_LENGTH - strlen($crc)) . $crc;
        }
        return $id;
    }

    private function __construct()
    {
    }
}