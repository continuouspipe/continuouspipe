<?php

namespace spec\ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\DockerCompose\ComponentsResolver;
use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class DockerComposeConfigurationAsDefaultSpec extends ObjectBehavior
{
    function let(ComponentsResolver $componentsResolver, LoggerInterface $logger)
    {
        $this->beConstructedWith($componentsResolver, $logger);
    }

    function it_should_generate_port_identifier_from_docker_compose_configuration(
        ComponentsResolver $componentsResolver, FlatFlow $flow, CodeReference $codeReference
    ) {
        $sampleDockerComposeComponent = DockerComposeComponent::fromParsed('elasticsearch', ['expose' => [9200]]);

        $componentsResolver->resolve($flow, $codeReference)->willReturn([$sampleDockerComposeComponent]);
        $configs = [
            [
                'tasks' => [
                    'task1' => [
                        'build' => [],
                        'deploy' => [],
                    ]
                ]
            ]
        ];
        $enhancedConfig = [
            [
                'tasks' => [
                    'task1' => [
                        'build' => [
                            'services' => []
                        ],
                        'deploy' => [
                            'services' => [
                                'elasticsearch' => [
                                    'specification' => [
                                        'ports' => [
                                            [
                                                'identifier' => 'elasticsear9200',
                                                'port' => 9200,
                                            ]
                                        ],
                                        'environment_variables' => [],
                                        'volumes' => [],
                                        'volume_mounts' => [],
                                    ],
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            [
                'tasks' => [
                    'task1' => [
                        'build' => [],
                        'deploy' => [],
                    ]
                ]
            ]
        ];

        $this->enhance($flow, $codeReference, $configs)->shouldReturn($enhancedConfig);
    }
}
