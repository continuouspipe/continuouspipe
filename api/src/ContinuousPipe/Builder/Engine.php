<?php

namespace ContinuousPipe\Builder;

class Engine
{
    const TYPES = ['docker', 'gcb'];
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        if (!in_array($type, self::TYPES)) {
            throw new \InvalidArgumentException('Invalid engine type, should be docker or gcb');
        }

        $this->type = $type;
    }

    public static function fromBuildIdentifier(string $buildIdentifier)
    {
        $parts = explode('--', $buildIdentifier);
        try {
            return isset($parts[1]) ? new self($parts[1]) : new self('docker');    
        } catch (\InvalidArgumentException $e) {
            return new self('docker');    
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
