<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\View\Tide;

class Branch
{
    private $name;
    private $tides;

    public function __construct(string $name, array $tides = [])
    {
        $this->name = $name;
        $this->tides = $tides;
    }

    public function withTides(array $tides)
    {
        return new self($this->name, $tides);
    }

    public function withTide(Tide $tide)
    {
        return new self($this->name, $this->mergeTides($this->tides, $tide));
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getTideUuids()
    {
        array_map(function(Tide $tide) {return $tide->getUuid();}, $this->tides);
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