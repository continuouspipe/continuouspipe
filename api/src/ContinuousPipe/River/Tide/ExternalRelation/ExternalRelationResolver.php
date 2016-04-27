<?php

namespace ContinuousPipe\River\Tide\ExternalRelation;

use Rhumsaa\Uuid\Uuid;

interface ExternalRelationResolver
{
    /**
     * @param Uuid $tideUuid
     *
     * @return ExternalRelation[]
     */
    public function getRelations(Uuid $tideUuid);
}
