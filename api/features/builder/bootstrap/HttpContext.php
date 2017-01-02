<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Guzzle\MatchingHandler;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\HistoryMiddleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class HttpContext implements Context
{
    /**
     * @var MatchingHandler
     */
    private $builderArchiveFactoryHttpMatchingHandler;

    /**
     * @var History
     */
    private $builderArchiveFactoryHttpHistory;

    public function __construct(
        MatchingHandler $builderArchiveFactoryHttpMatchingHandler,
        History $builderArchiveFactoryHttpHistory
    ) {
        $this->builderArchiveFactoryHttpMatchingHandler = $builderArchiveFactoryHttpMatchingHandler;
        $this->builderArchiveFactoryHttpHistory = $builderArchiveFactoryHttpHistory;
    }

    /**
     * @Given the URL :url will return the archive :fixture
     */
    public function theUrlWillReturnTheArchive($url, $fixture)
    {
        $this->builderArchiveFactoryHttpMatchingHandler->pushMatcher([
            'match' => function(RequestInterface $request) use ($url) {
                return $request->getUri() == $url;
            },
            'response' => new Response(200, [], file_get_contents(__DIR__.'/../fixtures/'.$fixture)),
        ]);
    }

    /**
     * @Then the archive should have been downloaded from the URL :url
     */
    public function theArchiveShouldHaveBeenDownloadedFromTheUrl($url)
    {
        foreach ($this->builderArchiveFactoryHttpHistory as $request) {
            /** @var Request $request */
            if ($request->getUri() == $url) {
                return $request;
            }
        }

        throw new \RuntimeException('This url was not downloaded apparently');
    }

    /**
     * @Then the archive should have been downloaded from the URL :url with the following headers:
     */
    public function theArchiveShouldHaveBeenDownloadedFromTheUrlWithTheFollowingHeaders($url, TableNode $headers)
    {
        $request = $this->theArchiveShouldHaveBeenDownloadedFromTheUrl($url);

        foreach ($headers->getHash() as $row) {
            if (!$request->hasHeader($row['name'])) {
                throw new \RuntimeException(sprintf('Request do not have the header "%s"', $row['name']));
            }

            $foundValue = $request->getHeader($row['name'])[0];
            if ($foundValue != $row['value']) {
                throw new \RuntimeException(sprintf(
                    'Expected "%s" for the header "%s" but found "%s"',
                    $row['value'],
                    $row['name'],
                    $foundValue
                ));
            }
        }
    }
}
