<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Storage\TideViewStorage;
use Ramsey\Uuid\UuidInterface;

interface TideRepository extends TideViewStorage
{
    /**
     * Find tides related to this flow UUID.
     *
     * @param UuidInterface $uuid
     *
     * @return TideList
     */
    public function findByFlowUuid(UuidInterface $uuid);

    /**
     * Find last `$limit` tides of this flow.
     *
     * @param UuidInterface $flowUuid
     * @param int $limit
     *
     * @return Tide[]
     */
    public function findLastByFlowUuid(UuidInterface $flowUuid, $limit);

    /**
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @return Tide[]
     */
    public function findByCodeReference(UuidInterface $flowUuid, CodeReference $codeReference);

    /**
     * Find all the tides of a given flow for the given branch. So we can record the comments.
     *
     * @param UuidInterface $flowUuid
     * @param string $branch
     * @param null $limit
     *
     * @return Tide[]
     */
    public function findByBranch(UuidInterface $flowUuid, $branch, $limit = null);

    /**
     * @param UuidInterface $flowUuid
     * @param string $branch
     *
     * @return Tide[]
     */
    public function findRunningByFlowUuidAndBranch(UuidInterface $flowUuid, $branch);

    /**
     * @param UuidInterface $flowUuid
     * @param string $branch
     *
     * @return Tide[]
     */
    public function findPendingByFlowUuidAndBranch(UuidInterface $flowUuid, $branch);

    /**
     * Find running tides for the given flow.
     *
     * @param UuidInterface $flowUuid
     *
     * @return Tide[]
     */
    public function findRunningByFlowUuid(UuidInterface $flowUuid);

    /**
     * Find tide representation by its UUID.
     *
     * @param UuidInterface $uuid
     *
     * @throws TideNotFound
     *
     * @return Tide
     */
    public function find(UuidInterface $uuid);

    /**
     * Find all tides.
     *
     * @return TideList
     */
    public function findAll();

    /**
     * Find tides by their generation UUID.
     *
     * @param UuidInterface $generationUuid
     * @param UuidInterface $flowUuid
     *
     * @return Tide[]
     */
    public function findByGenerationUuid(UuidInterface $flowUuid, UuidInterface $generationUuid);

    /**
     * Count the started tides by flow uuid
     *
     * @param UuidInterface $flowUuid
     * @param \DateTime $from
     * @return int
     */
    public function countStartedTidesByFlowSince(UuidInterface $flowUuid, \DateTime $from) : int;

    /**
     * Find the last X successful tides for this given flow and branch.
     *
     * @param UuidInterface $flowUuid
     * @param string $branch
     * @param int $limit
     *
     * @return Tide[]
     */
    public function findLastSuccessfulByFlowUuidAndBranch(UuidInterface $flowUuid, string $branch, int $limit) : array;

    /**
     * @param UuidInterface $flowUuid
     * @param \DateTimeInterface $left
     * @param \DateTimeInterface $right
     *
     * @return TideList
     */
    public function findByFlowBetween(UuidInterface $flowUuid, \DateTimeInterface $left, \DateTimeInterface $right) : TideList;
}
