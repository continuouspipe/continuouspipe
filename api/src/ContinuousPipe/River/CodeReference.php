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
    private $sha1;

    /**
     * @var null|string
     */
    private $branch;

    /**
     * @param CodeRepository $codeRepository
     * @param string         $sha1
     * @param string         $branch
     */
    public function __construct(CodeRepository $codeRepository, $sha1, $branch = null)
    {
        $this->codeRepository = $codeRepository;
        $this->sha1 = $sha1;
        $this->branch = $branch;
    }

    /**
     * @return CodeRepository
     */
    public function getRepository()
    {
        return $this->codeRepository;
    }

    /**
     * @return string
     */
    public function getCommitSha()
    {
        return $this->sha1;
    }

    /**
     * @return null|string
     */
    public function getBranch()
    {
        return $this->branch;
    }
}
