<?php

namespace ContinuousPipe\River\CodeRepository;

use JMS\Serializer\Annotation as JMS;

class Commit
{
    private $sha;
    private $url;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    public function __construct(string $sha, string $url, \DateTimeInterface $dateTime = null)
    {
        $this->sha = $sha;
        $this->url = $url;
        $this->dateTime = $dateTime;
    }

    public static function fromGitHubRepresentation(array $commit)
    {
        $datetime = isset($commit['timestamp']) ? new \DateTime($commit['timestamp']) : null;
        $url = str_replace(
            ['api.github.com/repos/', 'commits/' . $commit['sha']],
            ['github.com/', 'commit/' . $commit['sha']],
            $commit['url']
        );

        return new self($commit['sha'], $url, $datetime);
    }

    public function getSha(): string
    {
        return $this->sha;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}
