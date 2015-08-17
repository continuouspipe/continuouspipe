<?php

namespace ContinuousPipe\Adapter\Kubernetes\Tests\Repository;

use Kubernetes\Client\Model\Node;
use Kubernetes\Client\Model\NodeList;
use Kubernetes\Client\Repository\NodeRepository;

class InMemoryNodeRepository implements NodeRepository
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    /**
     * @return NodeList
     */
    public function findAll()
    {
        return new NodeList($this->nodes);
    }
}