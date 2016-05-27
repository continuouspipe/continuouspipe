<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\View;

use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideList;
use Doctrine\ORM\QueryBuilder;

class DoctrineTideList implements TideList
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var callable
     */
    private $transformer;

    /**
     * @param QueryBuilder $queryBuilder
     * @param callable $transformer
     */
    public function __construct(QueryBuilder $queryBuilder, callable $transformer)
    {
        $this->queryBuilder = $queryBuilder;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_map($this->transformer, $this->queryBuilder->getQuery()->getResult());
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
