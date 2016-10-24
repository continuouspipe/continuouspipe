<?php

namespace Task;

use Behat\Behat\Context\Context;
use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\River\WebHook\TraceableWebHookClient;
use ContinuousPipe\River\WebHook\WebHook;

class WebHookContext implements Context
{
    /**
     * @var TraceableWebHookClient
     */
    private $traceableWebHookClient;

    /**
     * @param TraceableWebHookClient $traceableWebHookClient
     */
    public function __construct(TraceableWebHookClient $traceableWebHookClient)
    {
        $this->traceableWebHookClient = $traceableWebHookClient;
    }

    /**
     * @Then a web-hook should be sent to :url
     */
    public function aWebHookShouldBeSentTo($url)
    {
        $webHooks = $this->traceableWebHookClient->getWebHooks();
        $matchingWebHooks = array_filter($webHooks, function(WebHook $webHook) use ($url) {
            return $webHook->getUrl() == $url;
        });

        if (count($matchingWebHooks) == 0) {
            throw new \RuntimeException('No matching web-hook found');
        }
    }

    /**
     * @Then the web-hook body should contain the code reference with the branch :branch
     */
    public function theWebHookBodyShouldContainTheCodeReferenceWithTheBranch($branch)
    {
        $webHooks = $this->traceableWebHookClient->getWebHooks();
        $matchingWebHooks = array_filter($webHooks, function(WebHook $webHook) use ($branch) {
            return $webHook->getCodeReference()->getBranch() == $branch;
        });

        if (count($matchingWebHooks) == 0) {
            throw new \RuntimeException('No matching web-hook found');
        }
    }

    /**
     * @Then the web-hook should contain the deployed environment :name that have the address :address
     */
    public function theWebHookShouldContainTheDeployedEnvironmentThatHaveTheAddress($name, $address)
    {
        $webHooks = $this->traceableWebHookClient->getWebHooks();
        $matchingWebHooks = array_filter($webHooks, function(WebHook $webHook) use ($name, $address) {
            $matchingPublicEndpoints = array_filter($webHook->getPublicEndpoints(), function(PublicEndpoint $publicEndpoint) use ($name, $address) {
                return $publicEndpoint->getName() == $name && $publicEndpoint->getAddress() == $address;
            });

            return count($matchingPublicEndpoints) > 0;
        });

        if (count($matchingWebHooks) == 0) {
            throw new \RuntimeException('No matching web-hook found');
        }
    }
}
