<?php

namespace ContinuousPipe\Builder\GoogleContainerBuilder;

use ContinuousPipe\Builder\Artifact;
use Psr\Http\Message\ResponseInterface;

interface BuildCreator
{
    public function startBuild(Artifact $sourceArtifact, string $gcbBuilderVersion): ResponseInterface;
}