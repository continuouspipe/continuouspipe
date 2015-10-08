<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

class DockerComposeVolume
{
    /**
     * @var string
     */
    private $definition;

    /**
     * @param string $definition
     */
    public function __construct($definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return bool
     */
    public function isHostMount()
    {
        return substr($this->getHostPath(), 0, 1) == '/';
    }

    /**
     * @return string
     */
    public function getHostPath()
    {
        return explode(':', $this->definition)[0];
    }

    /**
     * @return string
     */
    public function getMountPath()
    {
        $parts = explode(':', $this->definition);
        if (2 != count($parts)) {
            throw new ResolveException(sprintf(
                'The definition of the volume is wrong: %s',
                $this->definition
            ));
        }

        return $parts[1];
    }
}
