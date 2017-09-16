<?php

namespace ContinuousPipe\QuayIo;

class Repository
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $visibility;

    public function __construct(string $name, string $visibility = null)
    {
        $this->name = $name;
        $this->visibility = $visibility;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }
}
