<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\EarlyAccess\InMemoryEarlyAccessCodeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class EarlyAccessContext implements Context
{
    const LOGIN_URL = '/login/';
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var InMemoryEarlyAccessCodeRepository
     */
    private $activationCodeRepository;

    public function __construct(KernelInterface $kernel, InMemoryEarlyAccessCodeRepository $activationCodeRepository)
    {
        $this->kernel = $kernel;
        $this->activationCodeRepository = $activationCodeRepository;
    }

    /**
     * @When I open the link of the early access program and enter the code :code
     */
    public function iOpenTheLinkOfTheEarlyAccessProgramAndEnterTheCode($code)
    {
        $this->response = $this->kernel->handle(Request::create('/early-access/'.$code.'/enter', 'POST'));
    }

    /**
     * @Given the browser is redirected to the login page
     */
    public function theBrowserIsRedirectedToTheLoginPage()
    {
        $this->assertResponseStatusCode(Response::HTTP_FOUND);

        if (self::LOGIN_URL != $this->response->headers->get('Location')) {
            throw new UnexpectedValueException(
                sprintf(
                    'Expected to be redirected to "%s", but got "%s".',
                    self::LOGIN_URL,
                    $this->response->headers->get('Location')
                )
            );
        }
    }

    /**
     * @Then I should see a not found page
     */
    public function iShouldSeeANotFoundPage()
    {
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND);
    }

    private function assertResponseStatusCode(int $expectedStatusCode)
    {
        if ($this->response->getStatusCode() !== $expectedStatusCode) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf(
                'Expected status code %d but got %d',
                $expectedStatusCode,
                $this->response->getStatusCode()
            ));
        }
    }
}
