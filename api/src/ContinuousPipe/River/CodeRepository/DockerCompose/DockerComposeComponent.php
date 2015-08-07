<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

class DockerComposeComponent
{
    /**
     * @var array
     */
    private $parsed;

    /**
     * @param array $parsed
     */
    private function __construct(array $parsed)
    {
        $this->parsed = $parsed;
    }

    /**
     * @param array $parsed
     *
     * @return DockerComposeComponent
     */
    public static function fromParsed(array $parsed)
    {
        return new self($parsed);
    }

    /**
     * @return bool
     */
    public function hasToBeBuilt()
    {
        return array_key_exists('build', $this->parsed) && $this->parsed['build'];
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        if (array_key_exists('image', $this->parsed)) {
            return $this->parsed['image'];
        }

        $labels = $this->getLabels();
        if (array_key_exists('com.continuouspipe.image-name', $labels)) {
            return $labels['com.continuouspipe.image-name'];
        }

        throw new ResolveException('No `image` property nor `com.continuouspipe.image-name` label');
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        if (array_key_exists('labels', $this->parsed)) {
            return $this->parsed['labels'];
        }

        return [];
    }
}
