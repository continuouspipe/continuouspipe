<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Storage\TideViewStorage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

interface TideRepository extends TideViewStorage
{
    /**
     * Find tides related to this flow UUID.
     *
     * @param Uuid $uuid
     *
     * @return TideList
     */
    public function findByFlowUuid(Uuid $uuid);

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
     * @param Uuid $flowUuid
     * @param CodeReference $codeReference
     *
     * @return Tide[]
     */
    public function findByCodeReference(Uuid $flowUuid, CodeReference $codeReference);

    /**
     * Find all the tides of a given flow for the given branch. So we can record the comments.
     *
     * @param Uuid $flowUuid
     * @param string $branch
     * @param null $limit
     * 
     * @return Tide[]
     */
    public function findByBranch(Uuid $flowUuid, $branch, $limit = null);

    /**
     * @param Uuid $flowUuid
     * @param string $branch
     *
     * @return Tide[]
     */
    public function findRunningByFlowUuidAndBranch(Uuid $flowUuid, $branch);

    /**
     * @param Uuid $flowUuid
     * @param string $branch
     *
     * @return Tide[]
     */
    public function findPendingByFlowUuidAndBranch(Uuid $flowUuid, $branch);

    /**
     * Find running tides for the given flow.
     *
     * @param Uuid $flowUuid
     *
     * @return Tide[]
     */
    public function findRunningByFlowUuid(Uuid $flowUuid);

    /**
     * Find tide representation by its UUID.
     *
     * @param Uuid $uuid
     *
     * @throws TideNotFound
     *
     * @return Tide
     */
    public function find(Uuid $uuid);

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

}
