<?php

namespace ContinuousPipe\River;

class CodeReference
{
    /**
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @var string
     */
    private $reference;

    /**
     * @param CodeRepository $codeRepository
     * @param string         $reference
     */
    public function __construct(CodeRepository $codeRepository, $reference)
    {
        $this->reference = $reference;
        $this->codeRepository = $codeRepository;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return CodeRepository
     */
    public function getRepository()
    {
        return $this->codeRepository;
    }
}
