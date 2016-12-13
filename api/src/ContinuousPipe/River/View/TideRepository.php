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
     * @param int           $limit
     *
     * @return Tide[]
     */
    public function findLastByFlowUuid(UuidInterface $flowUuid, $limit);

    /**
     * @param Uuid          $flowUuid
     * @param CodeReference $codeReference
     *
     * @return Tide[]
     */
    public function findByCodeReference(Uuid $flowUuid, CodeReference $codeReference);

    /**
     * Find all the tides of a given flow for the given branch. So we can record the comments.
     *
     * @param Uuid          $flowUuid
     * @param CodeReference $codeReference
     *
     * @return Tide[]
     */
    public function findByBranch(Uuid $flowUuid, CodeReference $codeReference);

    /**
     * @param Uuid   $flowUuid
     * @param string $branch
     *
     * @return Tide[]
     */
    public function findRunningByFlowUuidAndBranch(Uuid $flowUuid, $branch);

    /**
     * @param Uuid   $flowUuid
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
}
