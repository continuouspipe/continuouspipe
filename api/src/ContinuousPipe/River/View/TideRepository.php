<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\TideNotFound;
use Rhumsaa\Uuid\Uuid;

interface TideRepository
{
    /**
     * Find tides related to this flow.
     *
     * @param Flow $flow
     *
     * @return Tide[]
     */
    public function findByFlow(Flow $flow);

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
