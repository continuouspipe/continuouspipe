<?php

namespace ContinuousPipe\DockerCompose\Transformer;

use ContinuousPipe\Model\Component;

class ComponentTransformer
{
    /**
     * @param string $identifier
     * @param array  $parsed
     *
     * @throws TransformException
     *
     * @return Component
     */
    public function load($identifier, array $parsed)
    {
        $image = array_key_exists('image', $parsed) ? $parsed['image'] : null;
        $scalability = new Component\Scalability(true, 1);
        $portMappings = [];
        $environmentVariables = [];
        $volumes = [];
        $volumeMounts = [];
        $labels = isset($parsed['labels']) ? $parsed['labels'] : [];
        $locked = array_key_exists('com.continuouspipe.update', $labels) && $labels['com.continuouspipe.update'] == 'lock';
        $publiclyVisible = array_key_exists('com.continuouspipe.visibility', $labels) && $labels['com.continuouspipe.visibility'] == 'public';
        $accessibility = new Component\Accessibility(true, $publiclyVisible);

        if (isset($parsed['environment'])) {
            foreach ($parsed['environment'] as $name => $value) {
                if (is_int($name)) {
                    $equalExploded = explode('=', $value);

                    if (count($equalExploded) == 1) {
                        // Ignore docker-compose's host variables
                        continue;
                    }

                    $name = array_shift($equalExploded);
                    $value = implode('=', $equalExploded);
                }

                $environmentVariables[] = new Component\EnvironmentVariable($name, $value);
            }
        }
        if (isset($parsed['expose'])) {
            foreach ($parsed['expose'] as $port) {
                $portMappings[] = new Component\Port('port-'.$port, (int) $port);
            }
        }

        if ($image === null && array_key_exists('com.continuouspipe.image-name', $labels)) {
            $image = $labels['com.continuouspipe.image-name'];
        }

        if (null === $image) {
            throw new TransformException(sprintf(
                'The component "%s" do not have any image',
                $identifier
            ));
        }

        $extensions = array_map(
            function ($name, $configuration) {
                return new Component\Extension($name, $configuration);
            },
            array_keys($labels),
            $labels
        );

        $source = new Component\Source($image);
        $specification = new Component\Specification($source, $accessibility, $scalability, $portMappings, $environmentVariables, $volumes, $volumeMounts);
        $component = new Component($identifier, $identifier, $specification, $extensions, $labels, $locked);

        return $component;
    }
}
