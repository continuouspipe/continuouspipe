<?php

namespace ContinuousPipe\River\CodeRepository;

class Branch
{
    private $name;
    private $tides;

    public function __construct(string $name, array $tides = [])
    {
        $this->name = $name;
        $this->tides = $tides;
    }
    
    public function withTides(array $tides)
    {
        return new self($this->name, $tides);
    }

    public function __toString()
    {
        return $this->name;
    }
}