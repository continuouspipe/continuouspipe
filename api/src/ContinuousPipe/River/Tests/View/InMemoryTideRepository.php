<?php

namespace ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideList;
use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\UuidInterface;

class InMemoryTideRepository implements TideRepository
{
    private $tideByCodeReference = [];
    private $tideByFlow = [];
    private $tides = [];

    /**
     * {@inheritdoc}
     */
    public function save(Tide $tide)
    {
        $tideUuid = (string) $tide->getUuid();
        $this->tides[$tideUuid] = $tide;

        // Save by flow UUID
        $flowUuid = (string) $tide->getFlowUuid();
        if (!array_key_exists($flowUuid, $this->tideByFlow)) {
            $this->tideByFlow[$flowUuid] = [];
        }
        $this->tideByFlow[$flowUuid][$tideUuid] = $tide;

        // Save by code reference
        $codeReference = $this->getCodeReferenceIdentifier($tide->getCodeReference());
        if (!array_key_exists($codeReference, $this->tideByCodeReference)) {
            $this->tideByCodeReference[$codeReference] = [];
        }
        $this->tideByCodeReference[$codeReference][$tideUuid] = $tide;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        if (!array_key_exists((string) $uuid, $this->tides)) {
            throw new TideNotFound(sprintf(
                'Tide with UUID "%s" not found',
                $uuid
            ));
        }

        return $this->tides[(string) $uuid];
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeReference(UuidInterface $flowUuid, CodeReference $codeReference)
    {
        $codeReferenceIdentifier = $this->getCodeReferenceIdentifier($codeReference);
        if (!array_key_exists($codeReferenceIdentifier, $this->tideByCodeReference)) {
            return [];
        }

        return array_values(array_filter($this->tideByCodeReference[$codeReferenceIdentifier], function (Tide $tide) use ($flowUuid) {
            return $tide->getFlowUuid() == $flowUuid;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findLastByFlowUuid(UuidInterface $flowUuid, $limit)
    {
        $tides = $this->findByFlowUuid($flowUuid);

        return array_slice($tides->toArray(), 0, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuidAndBranch(UuidInterface $flowUuid, $branch)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($branch) {
            return $tide->getCodeReference()->getBranch() == $branch && $tide->getStatus() == Tide::STATUS_RUNNING;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuid(UuidInterface $flowUuid)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) {
            return $tide->getStatus() == Tide::STATUS_RUNNING;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowUuid(UuidInterface $uuid)
    {
        $uuid = (string) $uuid;

        if (!array_key_exists($uuid, $this->tideByFlow)) {
            return new InMemoryTideList();
        }

        return new InMemoryTideList(
            $this->sortByCreationDateDesc(
                array_values($this->tideByFlow[$uuid])
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findByBranch(UuidInterface $flowUuid, $branch, $limit = null)
    {
        $tides = array_values(array_filter($this->tides, function (Tide $tide) use ($flowUuid, $branch) {
            return $tide->getFlowUuid() == $flowUuid && $tide->getCodeReference()->getBranch() == $branch;
        }));

        if (isset($limit)) {
            $tides = array_slice($tides, 0, $limit);
        }

        return $this->sortByCreationDateDesc($tides);
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingByFlowUuidAndBranch(UuidInterface $flowUuid, $branch)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($branch) {
            return $tide->getCodeReference()->getBranch() == $branch && $tide->getStatus() == Tide::STATUS_PENDING;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findLastSuccessfulByFlowUuidAndBranch(UuidInterface $flowUuid, string $branch, int $limit): array
    {
        $tides = array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($branch) {
            return $tide->getCodeReference()->getBranch() == $branch && $tide->getStatus() == Tide::STATUS_SUCCESS;
        }));

        return array_slice($tides, 0, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function findByGenerationUuid(UuidInterface $flowUuid, UuidInterface $generationUuid)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($generationUuid) {
            return $tide->getGenerationUuid() == $generationUuid;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return new InMemoryTideList($this->tides);
    }

    /**
     * {@inheritdoc}
     */
    public function countStartedTidesByFlowSince(UuidInterface $flowUuid, \DateTime $from): int
    {
        $tides = array_filter(
            $this->tides,
            function (Tide $tide) use ($from, $flowUuid) {
                return $tide->getStartDate() >= $from && $tide->getFlowUuid() == $flowUuid;
            }
        );
        return count($tides);
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowBetween(UuidInterface $flowUuid, \DateTimeInterface $left, \DateTimeInterface $right) : TideList
    {
        return new InMemoryTideList(
            array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($left, $right) {
                return $tide->getCreationDate() >= $left && $tide->getCreationDate() <= $right;
            }))
        );
    }

    /**
     * @param CodeReference $codeReference
     *
     * @return string
     */
    private function getCodeReferenceIdentifier(CodeReference $codeReference)
    {
        return sprintf(
            '%s:%s',
            $codeReference->getBranch(),
            $codeReference->getCommitSha()
        );
    }

    /**
     * @param Tide[] $tides
     *
     * @return Tide[]
     */
    private function sortByCreationDateDesc(array $tides)
    {
        usort($tides, function (Tide $left, Tide $right) {
            return $left->getCreationDate() > $right->getCreationDate() ? -1 : 1;
        });

        return $tides;
    }
}
