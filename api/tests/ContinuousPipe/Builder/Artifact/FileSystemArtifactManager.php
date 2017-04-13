<?php

namespace ContinuousPipe\Builder\Artifact;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Artifact;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerImageReader;
use ContinuousPipe\Builder\Image;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemArtifactManager implements ArtifactReader, ArtifactWriter, ArtifactRemover
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
        $localArtifactPath = $this->getArtifactPath($artifact);
        if (!file_exists($localArtifactPath)) {
            throw new ArtifactNotFound(sprintf('Artifact "%s" not found (searched: %s)', $artifact->getName(), $localArtifactPath));
        }

        return Archive\FileSystemArchive::fromStream(fopen($localArtifactPath, 'r'));
    }

    /**
     * {@inheritdoc}
     */
    public function write(Archive $source, Artifact $artifact, string $format = null)
    {
        $artifactStream = fopen($this->getArtifactPath($artifact), 'w');

        try {
            if (false === stream_copy_to_stream($source->read($format), $artifactStream)) {
                throw new ArtifactException('Something went wrong while copying stream to file');
            }
        } finally {
            fclose($artifactStream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Artifact $artifact)
    {
        $filesystem = new Filesystem();
        $localArtifactPath = $this->getArtifactPath($artifact);

        if (!$filesystem->exists($localArtifactPath)) {
            throw new ArtifactException('The artifact was not found');
        }

        $filesystem->remove($localArtifactPath);
    }

    /**
     * @param Artifact $artifact
     *
     * @return string
     */
    private function getArtifactPath(Artifact $artifact): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $artifact->getIdentifier();
    }
}
