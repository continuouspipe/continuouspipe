<?php

namespace Authenticator;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SubscriptionContext implements Context
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    public function __construct(
        KernelInterface $kernel
    ) {
        $this->kernel = $kernel;
    }

    /**
     * @When I request the list of available plans
     */
    public function iRequestTheListOfAvailablePlans()
    {
        $this->response = $this->kernel->handle(Request::create('/api/billing/plans'));
    }

    /**
     * @Then I should see the following plans:
     */
    public function iShouldSeeTheFollowingPlans(TableNode $expectedPlans)
    {
        $this->assertStatusCode(200);

        $plans = \GuzzleHttp\json_decode($this->response->getContent(), true);

        foreach ($expectedPlans->getHash() as $expectedPlan) {
            if ($this->hasChildInJson($plans, $expectedPlan)) {
                return;
            }
        }

        throw new \RuntimeException('Plan is not found');
    }

    /**
     * @When I request the list of available add-ons
     */
    public function iRequestTheListOfAvailableAddOns()
    {
        $this->response = $this->kernel->handle(Request::create('/api/billing/add-ons'));
    }

    /**
     * @Then I should see the following add-ons:
     */
    public function iShouldSeeTheFollowingAddOns(TableNode $expectedAddOns)
    {
        $this->assertStatusCode(200);
        $addOns = \GuzzleHttp\json_decode($this->response->getContent(), true);

        foreach ($expectedAddOns->getHash() as $expectedAddOn) {
            if (!$this->hasChildInJson($addOns, $expectedAddOn)) {
                throw new \RuntimeException(sprintf(
                    'Add-on %s is not found',
                    $expectedAddOn['identifier']
                ));
            }
        }
    }


    private function assertStatusCode(int $code)
    {
        if ($this->response->getStatusCode() != $code) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status %d but got %d',
                $code,
                $this->response->getStatusCode()
            ));
        }
    }

    private function hasChildInJson(array $collection, array $flattenedObject) : bool
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($collection as $item) {
            foreach ($flattenedObject as $key => $value) {
                if (strpos($key, '[') === false) {
                    $key = '['.str_replace('.', '][', $key).']';
                }

                $foundValue = $accessor->getValue($item, $key);
                if ($foundValue != $value) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }
}
