<?php

namespace ContinuousPipe\River\CodeRepository;

class PullRequest
{
    /**
     * @var string
     */
    private $identifier;
    /**
     * @var string
     */
    private $title;
    /**
     * @var Branch
     */
    private $branch;
    /**
     * @var string
     */
    private $url;

    public function __construct(string $identifier, string $title = null, Branch $branch = null, string $url = null)
    {
        $this->identifier = $identifier;
        $this->title = $title;
        $this->branch = $branch;
        $this->url = $url;
    }

    public static function github(string $identifier, string $address, string $title = null, Branch $branch = null)
    {
        return new self($identifier, $title, $branch, $address . '/pull/' . $identifier);
    }

    public static function bitbucket(string $identifier, string $address, string $title = null, Branch $branch = null)
    {
        return new self(
            $identifier, $title, $branch, str_replace(
            'https://api.bitbucket.org/2.0/repositories/',
            'https://bitbucket.org/',
            $address . '/pull-requests/' . $identifier
        )
        );
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function withBranch(Branch $branch)
    {
        return new self($this->identifier, $this->title, $branch, $this->url);
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getUrl()
    {
        return $this->url;
    }

}
