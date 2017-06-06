<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

class Branch
{
    private $name;
    private $tides;
    private $pinned;

    public function __construct(string $name, array $tides = [], bool $pinned = false)
    {
        $this->name = $name;
        $this->tides = $tides;
        $this->pinned = $pinned;
    }

    public function withTides(array $tides)
    {
        return new self($this->name, $tides, $this->pinned);
    }

    public function withTide(Tide $tide)
    {
        return new self($this->name, $this->mergeTides($this->tides, $tide), $this->pinned);
    }

    public function pinned()
    {
        return new self($this->name, $this->tides, true);
    }

    public function unpinned()
    {
        return new self($this->name, $this->tides, false);
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getTideUuids()
    {
        return array_map(function(Tide $tide) {return $tide->getUuid();}, $this->tides);
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    private function mergeTides(array $tides, Tide $toMerge)
    {
        $newTides = [];
        $replaced = false;
        foreach($tides as $tide) {
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
}