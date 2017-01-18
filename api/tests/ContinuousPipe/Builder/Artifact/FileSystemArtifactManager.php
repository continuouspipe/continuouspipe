<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerImageReader;
use ContinuousPipe\Builder\Image;

class FileSystemArtifactManager implements ArtifactReader, ArtifactWriter
{
    /**
     * @var string
     */
    private $directory;

    public function __construct()
    {
        $this->directory = Archive\FileSystemArchive::createDirectory('fs-artifacts');
    }

    /**
     * {@inheritdoc}
     */
    public function read(Artifact $artifact) : Archive
    {
        $localArtifactPath = $this->directory.DIRECTORY_SEPARATOR.$artifact->getIdentifier();
        if (!file_exists($localArtifactPath)) {
            throw new ArtifactException(sprintf('Artifact "%s" is not found (searched: %s)', $artifact->getIdentifier(), $localArtifactPath));
        }

        return Archive\FileSystemArchive::fromStream(fopen($localArtifactPath, 'r'));
    }

    /**
     * {@inheritdoc}
     */
    public function write(Archive $source, Artifact $artifact)
    {
        $localArtifactPath = $this->directory.DIRECTORY_SEPARATOR.$artifact->getIdentifier();
        $artifactStream = fopen($localArtifactPath, 'w');

        try {
            if (false === stream_copy_to_stream($source->read(), $artifactStream)) {
                throw new ArtifactException('Something went wrong while copying stream to file');
            }
        } finally {
            fclose($artifactStream);
        }
    }
}
