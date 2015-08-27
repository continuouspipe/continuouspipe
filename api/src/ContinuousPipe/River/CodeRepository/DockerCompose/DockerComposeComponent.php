<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\DockerCompose\Transformer\ComponentTransformer;
use ContinuousPipe\DockerCompose\Transformer\TransformException;

class DockerComposeComponent
{
    /**
     * @var ComponentTransformer
     */
    private $componentTransformer;

    /**
     * @var array
     */
    private $parsed;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param array $parsed
     */
    private function __construct($name, array $parsed)
    {
        $this->componentTransformer = new ComponentTransformer();
        $this->parsed = $parsed;
        $this->name = $name;
    }

    /**
     * @param string $name
     * @param array $parsed
     * @return DockerComposeComponent
     */
    public static function fromParsed($name, array $parsed)
    {
        return new self($name, $parsed);
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
        return $this->getComponent()->getSpecification()->getSource()->getImage();
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->getComponent()->getLabels();
    }

    /**
     * @return \ContinuousPipe\Model\Component
     */
    private function getComponent()
    {
        try {
            return $this->componentTransformer->load($this->name, $this->parsed);
        } catch (TransformException $e) {
            throw new ResolveException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return string
     */
    public function getDockerfilePath()
    {
        return array_key_exists('dockerfile', $this->parsed) ? $this->parsed['dockerfile'] : '';
    }

    /**
     * @return string
     */
    public function getBuildDirectory()
    {
        return array_key_exists('build', $this->parsed) ? $this->parsed['build'] : '.';
    }
}
