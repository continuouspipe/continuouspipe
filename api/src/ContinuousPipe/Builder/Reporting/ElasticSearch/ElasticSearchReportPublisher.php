<?php

namespace ContinuousPipe\Builder\Reporting\ElasticSearch;

use ContinuousPipe\Builder\Reporting\ReportException;
use ContinuousPipe\Builder\Reporting\ReportPublisher;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class ElasticSearchReportPublisher implements ReportPublisher
{
    /**
     * @var \Elasticsearch\Client|null
     */
    private $client;

    public function __construct(string $elasticSearchHostname = null, string $apiKey = null)
    {
        if (null !== $elasticSearchHostname) {
            $this->client = $this->createClient($elasticSearchHostname, $apiKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $buildIdentifier, array $report)
    {
        if (null === $this->client) {
            return;
        }

        try {
            $this->client->index([
                'type' => 'build',
                'index' => 'build-'.date('d.m.Y'),
                'id' => $buildIdentifier,
                'body' => $report,
            ]);
        } catch (\Exception $e) {
            throw new ReportException('Something went wrong while publishing the report', $e->getCode(), $e);
        }
    }

    private function createClient($elasticSearchHostname, $apiKey) : Client
    {
        $apiKeyHandler = function(callable $next, string $apiKey) {
            return function (array $request) use ($next, $apiKey) {
                if (isset($request['query_string'])) {
                    $request['query_string'] .= '&apikey='.$apiKey;
                } else {
                    $request['query_string'] = 'apikey='.$apiKey;
                }

                return $next($request);
            };
        };

        return ClientBuilder::create()
            ->setHandler(
                $apiKeyHandler(ClientBuilder::defaultHandler(), $apiKey)
            )
            ->setHosts([
                $elasticSearchHostname,
            ])
            ->build()
        ;
    }
}
