<?php

namespace ContinuousPipe\Builder;

class Engine
{
    const TYPES = ['docker', 'gcb'];
    const DEFAULT = 'docker';
    
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
        return new self(self::DEFAULT);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
