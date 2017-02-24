<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Authenticator\EarlyAccess\InMemoryEarlyAccessCodeRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    public function __construct(
        KernelInterface $kernel,
        InMemoryEarlyAccessCodeRepository $activationCodeRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->kernel = $kernel;
        $this->activationCodeRepository = $activationCodeRepository;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @When I open the link of the early access program and enter the code :code
     * @Given the user opens the link of the early access program and enter the code :code
     */
    public function iOpenTheLinkOfTheEarlyAccessProgramAndEnterTheCode($code)
    {
        $token = $this->csrfTokenManager->getToken('early_access_code');
        $this->response = $this->kernel->handle(Request::create('/early-access/', 'POST', [
                'early_access_code' => [
                    'code' => $code,
                    '_token' => $token->getValue(),
                ]
            ]
        ));
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
     * @Then I should see an error on the page
     */
    public function iShouldSeeAnErrorOnThePage()
    {
        $this->assertResponseStatusCode(Response::HTTP_OK);

        $this->assertResponseHtmlContainsError();
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

    private function assertResponseHtmlContainsError()
    {
        $crawler = new Crawler($this->response->getContent());

        if ($crawler->filter('div.alert')->count() == 0) {
            throw new \RuntimeException('Page expected to contain an error, but not found.');
        }
    }
}
