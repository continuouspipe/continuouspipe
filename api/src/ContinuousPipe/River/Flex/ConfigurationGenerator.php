<?php

namespace ContinuousPipe\River\Flex;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\Flow\EncryptedVariable\EncryptedVariableVault;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

class ConfigurationGenerator
{
    /**
     * @var EncryptedVariableVault
     */
    private $encryptedVariableVault;

    /**
     * @var array
     */
    private $defaultVariables;

    /**
     * @param EncryptedVariableVault $encryptedVariableVault
     * @param array $defaultVariables
     */
    public function __construct(EncryptedVariableVault $encryptedVariableVault, array $defaultVariables)
    {
        $this->encryptedVariableVault = $encryptedVariableVault;
        $this->defaultVariables = $defaultVariables;
    }

    public function generate(RelativeFileSystem $fileSystem, FlatFlow $flow)
    {
        if (null === ($configuration = $flow->getFlexConfiguration())) {
            throw new \InvalidArgumentException('The flow must be flexed in order to generate the configuration');
        }

        $flowUuid = $flow->getUuid()->toString();

        $dockerFile = <<<EOF
FROM quay.io/continuouspipe/symfony-php7.1-nginx:latest
ARG SYMFONY_ENV=prod
ARG APP_ENV=prod

ENV WEB_DIRECTORY=public
ENV SYMFONY_APP_ENDPOINT=/index.php

COPY . /app/
WORKDIR /app

RUN container build
EOF;

        try {
            $variables = (new Dotenv())->parse($fileSystem->getContents('.env.dist'));
        } catch (FileNotFound $e) {
            $variables = [];
        }

        $dockerComposeServices = [];
        $buildServices = [
            'app' => [
                'image' => 'quay.io/continuouspipe-flex/flow-'.$flowUuid,
                'naming_strategy' => 'sha1',
            ],
        ];

        $appDeployServices = [
            'app' => [
                'endpoints' => [
                    [
                        'name' => 'app',
                        'cloud_flare_zone' => [
                            'zone_identifier' => '${CLOUD_FLARE_ZONE}',
                            'proxied' => true,
                            'authentication' => [
                                'email' => '${CLOUD_FLARE_EMAIL}',
                                'api_key' => '${CLOUD_FLARE_API_KEY}',
                            ]
                        ],
                        'ingress' => [
                            'class' => 'nginx',
                            'host_suffix' => '-'.$configuration->getSmallIdentifier().'-flex.continuouspipe.net',
                        ]
                    ]
                ],
                'deployment_strategy' => [
                    'readiness_probe' => [
                        'type' => 'tcp',
                        'port' => 80,
                    ],
                ],
            ]
        ];

        $tasks = [
            '0_images' => [
                'build' => [
                    'services' => $buildServices,
                ]
            ],
            '2_app_deployment' => [
                'deploy' => [
                    'services' => $appDeployServices
                ]
            ]
        ];

        if (isset($variables['DATABASE_URL'])) {
            $variables['DATABASE_URL'] = 'postgres://app:app@database/app';

            $dockerComposeServices['database'] = [
                'image' => 'postgres',
                'environment' => [
                    'POSTGRES_PASSWORD=app',
                    'POSTGRES_USER=app',
                    'POSTGRES_DB=app',
                ],
                'expose' => [
                    5432,
                ]
            ];

            $tasks['1_database_deployment'] = [
                'deploy' => [
                    'services' => [
                        'database' => [
                            'deployment_strategy' => [
                                'readiness_probe' => [
                                    'type' => 'tcp',
                                    'port' => 5432,
                                ],
                            ],
                        ]
                    ]
                ]
            ];
        }

        // Sort tasks by name
        ksort($tasks);

        $continuousPipeFile = [
            'variables' => $this->generateVariables($flow),
            'defaults' => [
                'cluster' => 'flex',
                'environment' => [
                    'name' => '\''.$flowUuid.'-\' ~ code_reference.branch',
                ],
            ],
            'tasks' => $tasks,
        ];

        $dockerComposeServices['app'] = [
            'build' => '.',
            'environment' => $this->generateDockerComposeEnvironmentFromVariables($variables),
            'expose' => [
                443,
            ],
        ];

        $dockerComposeFile = [
            'version' => '2',
            'services' => $dockerComposeServices,
        ];

        return [
            'Dockerfile' => $dockerFile,
            'docker-compose.yml' => Yaml::dump($dockerComposeFile),
            'continuous-pipe.yml' => Yaml::dump($continuousPipeFile),
        ];
    }

    private function generateDockerComposeEnvironmentFromVariables(array $variables)
    {
        $variableDefinitions = [];

        foreach ($variables as $key => $value) {
            $variableDefinitions[] = $key.'='.$value;
        }

        return $variableDefinitions;
    }

    private function generateVariables(FlatFlow $flow)
    {
        $variables = [];

        foreach ($this->defaultVariables as $key => $value) {
            $variables[] = [
                'name' => $key,
                'encrypted_value' => $this->encryptedVariableVault->encrypt($flow->getUuid(), $value),
            ];
        }

        return $variables;
    }
}
