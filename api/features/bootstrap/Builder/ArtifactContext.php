<?php

namespace Builder;

use Behat\Behat\Context\Context;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Artifact\FileSystemArtifactManager;

class ArtifactContext implements Context
{
    /**
     * @var Artifact\ArtifactWriter
     */
    private $artifactWriter;
    /**
     * @var Artifact\TracedArtifactRemover
     */
    private $tracedArtifactRemover;

    public function __construct(Artifact\ArtifactWriter $artifactWriter, Artifact\TracedArtifactRemover $tracedArtifactRemover)
    {
        $this->artifactWriter = $artifactWriter;
        $this->tracedArtifactRemover = $tracedArtifactRemover;
    }

    /**
     * @Given the artifact :uuid contains the fixtures folder :folder
     */
    public function theArtifactContainsTheFixturesFolder($uuid, $fixturesFolder)
    {
        $this->artifactWriter->write(
            FileSystemArchive::copyFrom(__DIR__.'/../../builder/fixtures/'.$fixturesFolder),
            new Artifact($uuid, '')
        );
    }

    /**
     * @Then the artifact :identifier should not have been deleted
     */
    public function theArtifactShouldNotHaveBeenDeleted($identifier)
    {
        foreach ($this->tracedArtifactRemover->getRemoved() as $artifact) {
            if ($artifact->getIdentifier() == $identifier) {
                throw new \RuntimeException('Artifact found in the remover history');
            }
        }
    }

    /**
     * @Then the artifact :identifier should have been deleted
     */
    public function theArtifactShouldHaveBeenDeleted($identifier)
    {
        foreach ($this->tracedArtifactRemover->getRemoved() as $artifact) {
            if ($artifact->getIdentifier() == $identifier) {
                return;
            }
        }

        throw new \RuntimeException('Artifact not found in the remover history');
    }
}
