<?php

namespace ContinuousPipe\River\CodeRepository;

use JMS\Serializer\Annotation as JMS;

class Commit
{
    private $sha;
    private $url;

    public function __construct(string $sha, string $url)
    {
        $this->sha = $sha;
        $this->url = $url;
    }

    static public function fromShaAndGitubApiUrl(string $sha, string $url)
    {
        return new self($sha, self::toGithubWebsiteUrl($sha, $url));
    }

    private static function toGithubWebsiteUrl(string $sha, string $url)
    {
        return str_replace(
            ['api.github.com/repos/', 'commits/' . $sha],
            ['github.com/', 'commit/' . $sha],
            $url
        );
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

}