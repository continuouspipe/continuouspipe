<?php

namespace ContinuousPipe\River\Filter\FilterHash;

use Ramsey\Uuid\UuidInterface;

interface FilterHashRepository
{
    /**
     * @param UuidInterface $uuid
     *
     * @return FilterHash|null
     */
    public function findByTideUuid(UuidInterface $uuid);

    /**
     * @param FilterHash $filterHash
     *
     * @return FilterHash
     */
    public function save(FilterHash $filterHash);
}
