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

        $dockerComposeFile = <<<EOF
version: '2'
services:
    app:
        build: .
        environment: {$this->generateDockerComposeEnvironmentFromVariables($variables)}
        expose: [ 443 ]
EOF;

        $continuousPipeFile = <<<EOF
variables: {$this->generateEncryptedVariables($flow)}
      
defaults:
    cluster: flex-cluster
    environment:
        name: "'{$flowUuid}' ~ code_reference.branch"
    
tasks:
    images:
        build:
            services:
                app:
                    image: quay.io/continuouspipe-flex/flow-{$flowUuid}
                    naming_strategy: sha1
    
    app_deployment:
        deploy:
            services:
                app:
                    endpoints:
                        - name: app
                          cloud_flare_zone:
                              zone_identifier: \${CLOUD_FLARE_ZONE}
                              authentication:
                                  email: \${CLOUD_FLARE_EMAIL}
                                  api_key: \${CLOUD_FLARE_API_KEY}
                          ingress:
                              class: nginx
                              host_suffix: '{$configuration->getSmallIdentifier()}-flex.continuouspipe.net'
EOF;

        return [
            'Dockerfile' => $dockerFile,
            'docker-compose.yml' => $dockerComposeFile,
            'continuous-pipe.yml' => $continuousPipeFile,
        ];
    }

    private function generateDockerComposeEnvironmentFromVariables(array $variables)
    {
        $variableDefinitions = [];

        foreach ($variables as $key => $value) {
            $variableDefinitions[] = $key.'='.$value;
        }

        return '['.implode(',', $variableDefinitions).']';
    }

    private function generateEncryptedVariables(FlatFlow $flow)
    {
        $encryptedVariables = [];

        foreach ($this->defaultVariables as $key => $value) {
            $encryptedVariables[] = [
                'name' => $key,
                'encrypted_value' => $this->encryptedVariableVault->encrypt($flow->getUuid(), $value),
            ];
        }

        return Yaml::dump($encryptedVariables, 0);
    }
}
