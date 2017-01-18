<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\FileSystemArtifactManager;

class ArtifactContext implements Context
{
    /**
     * @var FileSystemArtifactManager
     */
    private $fileSystemArtifactManager;

    /**
     * @param FileSystemArtifactManager $fileSystemArtifactManager
     */
    public function __construct(FileSystemArtifactManager $fileSystemArtifactManager)
    {
        $this->fileSystemArtifactManager = $fileSystemArtifactManager;
    }

    /**
     * @Given the artifact :uuid contains the fixtures folder :folder
     */
    public function theArtifactContainsTheFixturesFolder($uuid, $fixturesFolder)
    {
        $this->fileSystemArtifactManager->append(
            new Artifact($uuid, ''),
            FileSystemArchive::copyFrom(__DIR__.'/../fixtures/'.$fixturesFolder)
        );
    }
}
