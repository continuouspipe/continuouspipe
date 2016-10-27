<?php

namespace ContinuousPipe\River\Tests\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\Uuid;

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
        $flowUuid = (string) $tide->getFlow()->getUuid();
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
    public function find(Uuid $uuid)
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
    public function findByCodeReference(Uuid $flowUuid, CodeReference $codeReference)
    {
        $codeReferenceIdentifier = $this->getCodeReferenceIdentifier($codeReference);
        if (!array_key_exists($codeReferenceIdentifier, $this->tideByCodeReference)) {
            return [];
        }

        return array_values(array_filter($this->tideByCodeReference[$codeReferenceIdentifier], function (Tide $tide) use ($flowUuid) {
            return $tide->getFlow()->getUuid() == $flowUuid;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findLastByFlow(Flow $flow, $limit)
    {
        $tides = $this->findByFlowUuid($flow->getUuid());

        return array_slice($tides->toArray(), 0, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuidAndBranch(Uuid $flowUuid, $branch)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($branch) {
            return $tide->getCodeReference()->getBranch() == $branch && $tide->getStatus() == Tide::STATUS_RUNNING;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuid(Uuid $flowUuid)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) {
            return $tide->getStatus() == Tide::STATUS_RUNNING;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowUuid(Uuid $uuid)
    {
        $uuid = (string) $uuid;

        if (!array_key_exists($uuid, $this->tideByFlow)) {
            return new InMemoryTideList();
        }

        $tides = array_values($this->tideByFlow[$uuid]);
        usort($tides, function (Tide $left, Tide $right) {
            return $left->getCreationDate() > $right->getCreationDate() ? -1 : 1;
        });

        return new InMemoryTideList($tides);
    }

    /**
     * {@inheritdoc}
     */
    public function findByBranch(Uuid $flowUuid, CodeReference $codeReference)
    {
        return array_values(array_filter($this->tides, function (Tide $tide) use ($flowUuid, $codeReference) {
            return $tide->getFlow()->getUuid() == $flowUuid && $tide->getCodeReference()->getBranch() == $codeReference->getBranch();
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingByFlowUuidAndBranch(Uuid $flowUuid, $branch)
    {
        return array_values(array_filter($this->findByFlowUuid($flowUuid)->toArray(), function (Tide $tide) use ($branch) {
            return $tide->getCodeReference()->getBranch() == $branch && $tide->getStatus() == Tide::STATUS_PENDING;
        }));
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
}
