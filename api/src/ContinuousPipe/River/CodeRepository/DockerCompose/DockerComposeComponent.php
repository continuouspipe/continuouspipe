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
     * @param array $parsed
     */
    private function __construct(array $parsed)
    {
        $this->componentTransformer = new ComponentTransformer();
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
            return $this->componentTransformer->load('component', $this->parsed);
        } catch (TransformException $e) {
            throw new ResolveException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
