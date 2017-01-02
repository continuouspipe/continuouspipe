<?php

use Behat\Behat\Context\Context;
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
                return;
            }
        }

        throw new \RuntimeException('This url was not downloaded apparently');
    }
}
