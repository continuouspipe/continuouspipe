<?php

use ContinuousPipe\Model\Application;
use ContinuousPipe\Model\Component;
use ContinuousPipe\Model\Environment;
use ContinuousPipe\Model\Status;

class SerializationTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializationAndDeserializationOfComponent()
    {
        $component = new Component(
            'identifier',
            'name',
            new Component\Specification(
                new Component\Source('image', 'tag', 'repo'),
                new Component\Accessibility(true, true),
                new Component\Scalability(true, 10),
                [new Component\Port('http', 80)],
                [new Component\EnvironmentVariable('foo', 'bar')],
                [
                    new ContinuousPipe\Model\Component\Volume\EmptyDirectory('empty'),
                    new Component\Volume\HostPath('docker', '/var/run/docker.sock')
                ],
                [new Component\VolumeMount('empty', '/empty')],
                ['my', 'command'],
                new Component\RuntimePolicy(true),
                new Component\Resources(
                    new Component\ResourcesRequest('1m', '1G'),
                    new Component\ResourcesRequest('2m', '4G')
                )
            ),
            [],
            [],
            new ContinuousPipe\Model\Component\Status(
                Status::HEALTHY,
                ['public-end-point'],
                [
                    new Component\Status\ContainerStatus(
                        'web',
                        Status::HEALTHY,
                        0
                    )
                ]
            ),
            new Component\DeploymentStrategy(
                new Component\Probe\Http(
                    '/foo',
                    443,
                    'localhost',
                    'https',
                    10,
                    9,
                    8,
                    7,
                    6
                ),
                new Component\Probe\Http('/healthz', 80),
                false,
                true,
                true
            )
        );

        $serializer = $this->getSerializer();
        $serialized = $serializer->serialize($component, 'json');
        $deserialized = $serializer->deserialize($serialized, Component::class, 'json');

        $this->assertEquals($component, $deserialized);
    }

    public function testSerializationAndDeserializationOfEnvironment()
    {
        $environment = new Environment(
            'identifier',
            'name',
            [],
            new Application(
                'identifier',
                'name'
            ),
            [
                'my' => 'label',
            ]
        );

        $serializer = $this->getSerializer();
        $serialized = $serializer->serialize($environment, 'json');
        $deserialized = $serializer->deserialize($serialized, Environment::class, 'json');

        $this->assertEquals($environment, $deserialized);
    }

        /**
     * @return \JMS\Serializer\Serializer
     */
    private function getSerializer()
    {
        return JMS\Serializer\SerializerBuilder::create()
            ->addMetadataDir(__DIR__.'/../../../src/ContinuousPipe/Model/Resources/serializer', 'ContinuousPipe\Model')
            ->build();
    }
}
