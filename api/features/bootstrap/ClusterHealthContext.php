<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use LogStream\Tests\InMemoryLogClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class ClusterHealthContext implements Context
{

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var HandlerStack
     */
    private $httpHandlerStack;

    /**
     * @var \Symfony\Component\HttpFoundation\Response|null
     */
    private $response;
    private $logStream;

    public function __construct(HandlerStack $httpHandlerStack, KernelInterface $kernel, InMemoryLogClient $logStream)
    {
        $this->httpHandlerStack = $httpHandlerStack;
        $this->kernel = $kernel;
        $this->logStream = $logStream;
    }

    /**
     * @Given the cluster with the address :address will have the following problems:
     */
    public function theClusterWithTheAddressWillHaveTheFollowingProblems($address, TableNode $table)
    {
        $this->httpHandlerStack->setHandler(new MockHandler([
            new Response(200, [], json_encode($table->getHash())),
        ]));
    }

    /**
     * @Then I should see a :type log event in the log stream with message :message
     */
    public function iShouldSeeALogEventInTheLogStreamWithMessage($type, $message)
    {
        $matching = array_filter(
            $this->logStream->getLogs(),
            function (array $entry) use($type, $message) {
                return $entry['type'] == $type && $entry['contents'] = $message;
            }
        );
        if (!$matching) {
            throw new \UnexpectedValueException(
                sprintf('Expected to find an event %s with message %s, none found', $type, $message)
            );
        }
    }
}
