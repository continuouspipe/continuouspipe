<?php

namespace ContinuousPipe\River;

class CodeReference
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @param string $reference
     */
    public function __construct($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }
}
