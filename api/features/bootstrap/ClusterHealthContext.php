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

    /**
     * @var LogStream\Tests\InMemoryLogClient
     */
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
        $matching = $this->findEvent($type, $message);

        if (count($matching) == 0) {
            throw new \UnexpectedValueException(
                sprintf('Expected to find an event %s with message %s, none found', $type, $message)
            );
        }
    }

    /**
     * @Then I should not see a :type log event in the log stream with message :message
     */
    public function iShouldNotSeeALogEventInTheLogStreamWithMessage($type, $message)
    {
        $matching = $this->findEvent($type, $message);
        if (count($matching) > 0) {
            throw new \UnexpectedValueException(
                sprintf('Expected to find no %s event with message %s, found %d', $type, $message, count($matching))
            );
        }
    }

    private function findEvent($type, $message)
    {
        return array_filter($this->logStream->getLogs(), function (array $entry) use ($type, $message) {
            if (!isset($entry['type'])) {
                return false;
            }

            if ($entry['type'] == 'events') {

                if (empty($entry['events'])) {
                    return false;
                }

                foreach($entry['events'] as $event) {
                    if ($event['message'] == $message) {
                        return true;
                    }
                }

                return false;
            }

            if (empty($entry['contents'])) {
                return false;
            }
            return $entry['type'] == $type && $entry['contents'] == $message;
        });
    }
}
