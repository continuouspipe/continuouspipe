<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;
use JMS\Serializer\Annotation as JMS;

class Branch
{
    /**
     * @JMS\Type("string")
     */
    private $name;
    private $tides;
    private $pinned;
    private $latestCommit;
    /**
     * @var string
     */
    private $url;

    public function __construct(
        string $name,
        array $tides = [],
        bool $pinned = false,
        Commit $latestCommit = null,
        string $url = null
    ) {
        $this->name = $name;
        $this->tides = $tides;
        $this->pinned = $pinned;
        $this->latestCommit = $latestCommit;
        $this->url = $url;
    }

    public static function bitbucket(string $name, string $address)
    {
        return new self(
            $name,
            [],
            false,
            null,
            str_replace(
                'https://api.bitbucket.org/2.0/repositories/',
                'https://bitbucket.org/',
                $address . '/branch/' . $name
            )
        );
    }

    public static function github(string $name, string $address)
    {
        return new self(
            $name,
            [],
            false,
            null,
            str_replace(
                'api.github.com/repos/',
                'github.com/',
                $address
            ) . '/branch/' . $name
        );
    }

    public function withTides(array $tides)
    {
        return new self($this->name, $tides, $this->pinned, $this->latestCommit, $this->url);
    }

    public function withTide(Tide $tide)
    {
        return new self(
            $this->name,
            $this->mergeTides($this->tides, $tide),
            $this->pinned,
            $this->latestCommit,
            $this->url
        );
    }

    public function withLatestCommit(Commit $latestCommit)
    {
        return new self($this->name, $this->tides, $this->pinned, $latestCommit, $this->url);
    }

    public function pinned()
    {
        return new self($this->name, $this->tides, true, $this->latestCommit, $this->url);
    }

    public function unpinned()
    {
        return new self($this->name, $this->tides, false, $this->latestCommit, $this->url);
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getTideUuids()
    {
        return array_map(
            function (Tide $tide) {
                return $tide->getUuid();
            },
            $this->tides
        );
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    public function getTides()
    {
        return $this->tides;
    }

    private function mergeTides(array $tides, Tide $toMerge)
    {
        $newTides = [];
        $replaced = false;

        foreach ($tides as $tide) {
            if ($toMerge->getUuid() == $tide->getUuid()) {
                $newTides[] = $toMerge;
                $replaced = true;
            } else {
                $newTides[] = $tide;
            }
        }

        if (!$replaced) {
            $newTides[] = $toMerge;
        }

        return $newTides;
    }

    public function getLatestCommit()
    {
        return $this->latestCommit;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
