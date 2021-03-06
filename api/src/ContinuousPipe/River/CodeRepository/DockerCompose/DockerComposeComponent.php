<?php

namespace ContinuousPipe\River\CodeRepository\DockerCompose;

use ContinuousPipe\DockerCompose\Transformer\ComponentTransformer;
use ContinuousPipe\DockerCompose\Transformer\TransformException;

class DockerComposeComponent implements \JsonSerializable
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
     * @param array  $parsed
     */
    private function __construct($name, array $parsed)
    {
        $this->componentTransformer = new ComponentTransformer();
        $this->parsed = $parsed;
        $this->name = $name;
    }

    /**
     * @param string $name
     * @param array  $parsed
     *
     * @return DockerComposeComponent
     */
    public static function fromParsed($name, array $parsed)
    {
        return new self($name, $parsed);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        return array_key_exists('labels', $this->parsed) ? $this->parsed['labels'] : [];
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
        if (array_key_exists('dockerfile', $this->parsed)) {
            return $this->parsed['dockerfile'];
        }

        if (array_key_exists('build', $this->parsed) && is_array($this->parsed['build'])) {
            if (array_key_exists('dockerfile', $this->parsed['build'])) {
                return $this->parsed['build']['dockerfile'];
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getBuildDirectory()
    {
        if (array_key_exists('build', $this->parsed)) {
            if (is_string($this->parsed['build'])) {
                return $this->parsed['build'];
            }

            if (array_key_exists('context', $this->parsed['build'])) {
                return $this->parsed['build']['context'];
            }
        }

        return '';
    }

    /**
     * @return string|null
     */
    public function getCommand()
    {
        return array_key_exists('command', $this->parsed) ? $this->parsed['command'] : null;
    }

    /**
     * @return array
     */
    public function getExposedPorts()
    {
        return array_key_exists('expose', $this->parsed) ? $this->parsed['expose'] : [];
    }

    /**
     * @return string
     */
    public function getUpdatePolicy()
    {
        $labels = $this->getLabels();

        return array_key_exists('com.continuouspipe.update', $labels) ? $labels['com.continuouspipe.update'] : null;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        $labels = $this->getLabels();

        return array_key_exists('com.continuouspipe.visibility', $labels) ? $labels['com.continuouspipe.visibility'] : null;
    }

    /**
     * @return array
     */
    public function getEnvironmentVariables()
    {
        if (!isset($this->parsed['environment']) || !is_array($this->parsed['environment'])) {
            return [];
        }

        $variables = [];
        foreach ($this->parsed['environment'] as $name => $value) {
            if (is_int($name)) {
                $equalExploded = explode('=', $value);

                if (count($equalExploded) == 1) {
                    // Ignore docker-compose's host variables
                    continue;
                }

                $name = array_shift($equalExploded);
                $value = implode('=', $equalExploded);
            }

            $variables[$name] = $value;
        }

        return $variables;
    }

    /**
     * @return bool
     */
    public function isPrivileged()
    {
        return array_key_exists('privileged', $this->parsed) && $this->parsed['privileged'];
    }

    /**
     * @return DockerComposeVolume[]
     */
    public function getVolumes()
    {
        if (!array_key_exists('volumes', $this->parsed)) {
            return [];
        }

        if (!is_array($this->parsed['volumes'])) {
            throw new ResolveException('Wrong volumes definition');
        }

        return array_map(function ($definition) {
            return new DockerComposeVolume($definition);
        }, $this->parsed['volumes']);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        try {
            $imageName = $this->getImageName();
        } catch (ResolveException $e) {
            $imageName = null;
        }

        return [
            'name' => $this->getName(),
            'has_to_be_built' => $this->hasToBeBuilt(),
            'image_name' => $imageName,
            'visibility' => $this->getVisibility(),
            'update_policy' => $this->getUpdatePolicy(),
            'exposed_ports' => $this->getExposedPorts(),
        ];
    }
}
