<?php

namespace Authenticator;

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
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM team_membership');
        $connection->executeUpdate('DELETE FROM security_user');
        $connection->executeUpdate('DELETE FROM account_link');
        $connection->executeUpdate('DELETE FROM user_api_key');
        $connection->executeUpdate('DELETE FROM user_billing_profiles');
        $connection->executeUpdate('DELETE FROM flat_flow');
        $connection->executeUpdate('DELETE FROM cp_user');

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }
}
