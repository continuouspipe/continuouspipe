<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\GoogleContainerBuilder\BuildCreator;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class TraceableBuildCreator implements BuildCreator
{
    private $requests = [];

    public function startBuild(Artifact $sourceArtifact, string $gcbBuilderVersion): ResponseInterface
    {
        $this->requests[] = [
            'source' => $sourceArtifact
        ];
        return new Response(200, [], \GuzzleHttp\json_encode(['metadata' => ['build' => ['id' => 'build-id']]]));
    }

    public function getRequests()
    {
        return $this->requests;
    }
}
