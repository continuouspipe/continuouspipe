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
            if ($request->getMethod() != 'POST' || !preg_match('#\/repository$#', $request->getUri())) {
                return false;
            }

            $body = $request->getBody();
            $body->rewind();

            $contents = $body->getContents();
            $json = \GuzzleHttp\json_decode($contents, true);

            return $json['repository'] == $repositoryName;
        });
    }

    /**
     * @Then a quay.io robot account :robotAccountName should have been created
     */
    public function aQuayIoRobotAccountShouldHaveBeenCreated($robotAccountName)
    {
        $this->assertOneRequestMatching(function(Request $request) use ($robotAccountName) {
            return $request->getMethod() == 'PUT' && preg_match('#\/robots/'.$robotAccountName.'$#', $request->getUri());
        });
    }

    /**
     * @Then the quay.io user :username should have been granted access to the :repository repository
     */
    public function theQuayIoUserShouldHaveBeenGrantedAccessToTheRepository($username, $repository)
    {
        $this->assertOneRequestMatching(function(Request $request) use ($username, $repository) {
            $username = str_replace(['/', '+'], ['\\/', '\\+'], $username);
            $repository = str_replace(['/', '+'], ['\\/', '\\+'], $repository);

            return $request->getMethod() == 'PUT' && preg_match('#\/repository\/'.$repository.'\/permissions\/user\/'.$username.'$#', $request->getUri());
        });
    }

    /**
     * @Then the quay.io repository :repository should have been changed to a private repository
     */
    public function theQuayIoRepositoryShouldHaveBeenChangedToAPrivateRepository($repository)
    {
        $this->assertOneRequestMatching(function(Request $request) use ($repository) {
            $repository = str_replace(['/', '+'], ['\\/', '\\+'], $repository);

            return $request->getMethod() == 'POST' && preg_match('#\/repository\/'.$repository.'\/change-visibility$#', $request->getUri());
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
