<?php

namespace ContinuousPipe\Builder\Reporting\ElasticSearch;

use ContinuousPipe\Builder\Reporting\ReportException;
use ContinuousPipe\Builder\Reporting\ReportPublisher;
use Elasticsearch\ClientBuilder;

class ElasticSearchReportPublisher implements ReportPublisher
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([
                'https://7d94ab0e-8bd7-49d4-b75d-40580272be9f-es.logit.io/?apikey=56efc8a1-541b-43d2-a157-808b3660b8a5&pretty=true',
            ])
            ->build()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(string $buildIdentifier, array $report)
    {
        try {
            $this->client->index([
                'type' => 'build',
                'id' => $buildIdentifier,
                'body' => $report,
            ]);
        } catch (\Exception $e) {
            throw new ReportException('Something went wrong while publishing the report', $e->getCode(), $e);
        }
    }
}
