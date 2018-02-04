<?php
<<<CONFIG
packages:
    - "symfony/dotenv: ^4.0.0"
    - "symfony/yaml: ^4.0.0"
CONFIG;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Yaml\Yaml;

if (!class_exists(Dotenv::class)) {
    require __DIR__ . '/vendor/autoload.php';
}

$rootFolder = $argv[1];
$environmentFile = $argv[2];
$configMapTemplate = $argv[3];

$environment = (new Dotenv())->parse(file_get_contents($rootFolder.DIRECTORY_SEPARATOR.$environmentFile), $environmentFile);
$configurationDirectory = dirname($configMapTemplate);

// Generate the ConfigMap file
$configMapContents = Yaml::parse(file_get_contents($configMapTemplate), true);
$configMapContents['data'] = $environment;
file_put_contents($configMapTemplate, Yaml::dump($configMapContents, 4, 2));

// Generate the secrets from files
$secretsByDirectory = [];
foreach ($environment as $variable => $value) {
    if (strtoupper(substr($variable, -5)) !== '_PATH') {
        continue;
    }

    $filePath = $rootFolder.DIRECTORY_SEPARATOR.$value;
    if (!file_exists($filePath)) {
        echo 'WARNING: File for variable "'.$variable.'" does not exists. Secret will not be created.'."\n";

        continue;
    }

    $directory = substr($value, 0, $lastSlashPosition = strrpos($value, DIRECTORY_SEPARATOR));
    $fileName = substr($value, $lastSlashPosition + 1);
    if (!isset($secretsByDirectory[$directory])) {
        $secretsByDirectory[$directory] = [];
    }

    $secretsByDirectory[$directory][$fileName] = base64_encode(file_get_contents($filePath));
}

foreach ($secretsByDirectory as $directory => $secrets) {
    $secretName = 'secret-'.preg_replace('#([^a-zA-Z0-9]+)#', '-', $directory);

    file_put_contents($configurationDirectory.DIRECTORY_SEPARATOR.'00-'.$secretName.'.yaml', Yaml::dump([
        'apiVersion' => 'v1',
        'kind' => 'Secret',
        'metadata' => [
            'name' => $secretName,
        ],
        'type' => 'Opaque',
        'data' => $secrets
    ]));
}
