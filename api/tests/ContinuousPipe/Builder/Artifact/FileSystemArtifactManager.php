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

    /**
     * @var DockerImageReader
     */
    private $dockerImageReader;

    /**
     * @param DockerImageReader $dockerImageReader
     */
    public function __construct(DockerImageReader $dockerImageReader)
    {
        $this->dockerImageReader = $dockerImageReader;
        $this->directory = Archive\FileSystemArchive::createDirectory('fs-artifacts');
    }

    /**
     * {@inheritdoc}
     */
    public function read(Artifact $artifact, Archive $into) : Archive
    {
        $localArtifactPath = $this->directory.DIRECTORY_SEPARATOR.$artifact->getIdentifier();
        if (!file_exists($localArtifactPath)) {
            throw new ArtifactException(sprintf('Artifact "%s" is not found (searched: %s)', $artifact->getIdentifier(), $localArtifactPath));
        }

        try {
            $into->write($artifact->getPath(), Archive\FileSystemArchive::fromStream(fopen($localArtifactPath, 'r')));
        } catch (Archive\ArchiveException $e) {
            throw new ArtifactException('Unable to write artifact', $e->getCode(), $e);
        }

        return $into;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Image $source, Artifact $artifact)
    {
        try {
            $archive = $this->dockerImageReader->read($source, $artifact->getPath());
        } catch (DockerException $e) {
            throw new ArtifactException('Unable to create an archive from the image', $e->getCode(), $e);
        }

        $this->append($artifact, $archive);
    }

    /**
     * @param Artifact $artifact
     * @param Archive $archive
     *
     * @throws ArtifactException
     */
    public function append(Artifact $artifact, Archive $archive)
    {
        $localArtifactPath = $this->directory.DIRECTORY_SEPARATOR.$artifact->getIdentifier();
        $artifactStream = fopen($localArtifactPath, 'w');

        try {
            if (false === stream_copy_to_stream($archive->read(), $artifactStream)) {
                throw new ArtifactException('Something went wrong while copying stream to file');
            }
        } finally {
            fclose($artifactStream);
        }
    }
}
