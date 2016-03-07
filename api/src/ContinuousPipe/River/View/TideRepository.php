<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\TideNotFound;
use Rhumsaa\Uuid\Uuid;

interface TideRepository
{
    /**
     * Find tides related to this flow UUID.
     *
     * @param Uuid $uuid
     *
     * @return Tide[]
     */
    public function findByFlowUuid(Uuid $uuid);

    /**
     * Find last `$limit` tides of this flow.
     *
     * @param Flow $flow
     * @param int  $limit
     *
     * @return Tide[]
     */
    public function findLastByFlow(Flow $flow, $limit);

    /**
     * @param CodeReference $codeReference
     *
     * @return Tide[]
     */
    public function findByCodeReference(CodeReference $codeReference);

    /**
     * @param Uuid   $flowUuid
     * @param string $branch
     *
     * @return Tide[]
     */
    public function findRunningByFlowUuidAndBranch(Uuid $flowUuid, $branch);

    /**
     * Save the tide representation.
     *
     * @param Tide $tide
     */
    public function save(Tide $tide);

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
}
