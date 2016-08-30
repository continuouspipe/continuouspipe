<?php

namespace ContinuousPipe\River\Tests\Repository;

use ContinuousPipe\River\Filter\FilterHash\FilterHash;
use ContinuousPipe\River\Filter\FilterHash\FilterHashRepository;
use Ramsey\Uuid\UuidInterface;

class InMemoryFilterHashRepository implements FilterHashRepository
{
    private $filterHashes = [];

    /**
     * {@inheritdoc}
     */
    public function findByTideUuid(UuidInterface $uuid)
    {
        $key = $uuid->toString();

        if (!array_key_exists($key, $this->filterHashes)) {
            return null;
        }

        return $this->filterHashes[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function save(FilterHash $filterHash)
    {
        $key = $filterHash->getTideUuid()->toString();

        if (array_key_exists($key, $this->filterHashes)) {
            throw new \RuntimeException('Filter hash already exists');
        }

        $this->filterHashes[$key] = $filterHash;

        return $filterHash;
    }
}
