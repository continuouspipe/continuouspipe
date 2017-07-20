<?php

use Behat\Behat\Context\Context;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use GuzzleHttp\Psr7\Request;

class QuayIoContext implements Context
{
    /**
     * @var History
     */
    private $quayIoHttpHistory;

    /**
     * @param History $quayIoHttpHistory
     */
    public function __construct(History $quayIoHttpHistory)
    {
        $this->quayIoHttpHistory = $quayIoHttpHistory;
    }

    /**
     * @Then a quay.io repository :repositoryName should be created
     */
    public function aQuayIoRepositoryShouldBeCreated($repositoryName)
    {
        $this->assertOneRequestMatching(function(Request $request) use ($repositoryName) {
            return $request->getMethod() == 'POST' &&
                preg_match('#\/repository$#', $request->getUri());
        });
    }

    /**
     * @Then a quay.io robot account :robotAccountName should have been created with access to the :repositoryName repository
     */
    public function aQuayIoRobotAccountShouldHaveBeenCreatedWithAccessToTheRepository($robotAccountName, $repositoryName)
    {
        $this->assertOneRequestMatching(function(Request $request) use ($repositoryName, $robotAccountName) {
            return $request->getMethod() == 'PUT' &&
                preg_match('#\/robots/([a-z0-9-]+)$#', $request->getUri());
        });
    }

    private function assertOneRequestMatching(callable $matchingAssertion)
    {
        foreach ($this->quayIoHttpHistory as $request) {
            if ($matchingAssertion($request)) {
                return;
            }
        }

        throw new \RuntimeException('No request matching the given assertion');
    }
}
