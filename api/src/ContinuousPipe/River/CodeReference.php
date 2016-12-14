<?php

namespace ContinuousPipe\River;

use JMS\Serializer\Annotation as JMS;

class CodeReference
{
    /**
     * @JMS\Type("ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository")
     * @JMS\Groups({"Default"})
     *
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default"})
     *
     * @var string
     */
    private $sha1;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"Default"})
     *
     * @var null|string
     */
    private $branch;

    /**
     * @param CodeRepository $codeRepository
     * @param string         $sha1
     * @param string         $branch
     */
    public function __construct(CodeRepository $codeRepository, $sha1 = null, $branch = null)
    {
        $this->codeRepository = $codeRepository;
        $this->sha1 = $sha1;
        $this->branch = $branch;
    }

    /**
     * Create the default code reference for this given code repository.
     *
     * @param CodeRepository $codeRepository
     *
     * @return CodeReference
     */
    public static function repositoryDefault(CodeRepository $codeRepository)
    {
        return new self(
            $codeRepository,
            null,
            $codeRepository->getDefaultBranch() ?: 'master'
        );
    }

    /**
     * @return CodeRepository
     */
    public function getRepository()
    {
        return $this->codeRepository;
    }

    /**
     * @return null|string
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
