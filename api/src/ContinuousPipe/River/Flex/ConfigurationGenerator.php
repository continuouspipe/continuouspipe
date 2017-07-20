<?php

namespace ContinuousPipe\River\Flex;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Symfony\Component\Dotenv\Dotenv;

class ConfigurationGenerator
{
    public function generate(RelativeFileSystem $fileSystem, FlatFlow $flow)
    {
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
                              host_suffix: '{$flowUuid}-flex.continuouspipe.net'
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
}
