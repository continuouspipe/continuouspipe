<?php

namespace ContinuousPipe\River\CodeRepository;

class Branch
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}