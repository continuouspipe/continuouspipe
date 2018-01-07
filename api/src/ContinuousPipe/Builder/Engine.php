<?php

namespace ContinuousPipe\Builder;

class Engine
{
    const DOCKER = 'docker';
    const GOOGLE_CONTAINER_BUILDER = 'gcb';

    const TYPES = [
        self::DOCKER,
        self::GOOGLE_CONTAINER_BUILDER,
    ];
    
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
    
    public static function withDefault()
    {
        return new self(self::DOCKER);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
