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

    public function __construct(string $name, array $tides = [], bool $pinned = false, Commit $latestCommit = null)
    {
        $this->name = $name;
        $this->tides = $tides;
        $this->pinned = $pinned;
        $this->latestCommit = $latestCommit;
    }

    public function withTides(array $tides)
    {
        return new self($this->name, $tides, $this->pinned, $this->latestCommit);
    }

    public function withTide(Tide $tide)
    {
        return new self($this->name, $this->mergeTides($this->tides, $tide), $this->pinned, $this->latestCommit);
    }

    public function withLatestCommit(Commit $latestCommit)
    {
        return new self($this->name, $this->tides, $this->pinned, $latestCommit);
    }

    public function pinned()
    {
        return new self($this->name, $this->tides, true, $this->latestCommit);
    }

    public function unpinned()
    {
        return new self($this->name, $this->tides, false, $this->latestCommit);
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

}