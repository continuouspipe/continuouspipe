<?php

use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class SmokeContext implements Context
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @BeforeScenario @smoke
     */
    public function cleanDB()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }
}
