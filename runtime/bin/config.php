<?php
<<<CONFIG
packages:
    - "sroze/companienv: dev-master#3a41c38"
    - "symfony/process: ^4.0"
CONFIG;

use Companienv\Application;
use Companienv\Extension;
use Companienv\Companion;
use Companienv\DotEnv\Block;
use Companienv\DotEnv\Variable;
use Symfony\Component\Process\Process;

if (!class_exists('Companienv\Application')) {
    require __DIR__ . '/vendor/autoload.php';
}

$rootDirectory = dirname(dirname(__DIR__));
$application = new Application($rootDirectory);
$application->registerExtension(new class() implements Extension {
    private $populatedVariables = [];

    /**
     * {@inheritdoc}
     */
    public function getVariableValue(Companion $companion, Block $block, Variable $variable)
    {
        if (null === ($attribute = $block->getAttribute('rsa-pair')) || !in_array($variable->getName(), $attribute->getVariableNames())) {
            return null;
        }

        if (isset($this->populatedVariables[$variable->getName()])) {
            return $this->populatedVariables[$variable->getName()];
        }

        if (!$companion->askConfirmation(sprintf(
            'Variables %s represents an RSA public/private key. Do you want to automatically generate them? (y) ',
            implode(' and ', array_map(function($variable) {
                return '<comment>'.$variable.'</comment>';
            }, $attribute->getVariableNames()))
        ))) {
            // Ensure we don't ask anymore for this variable pair
            foreach ($attribute->getVariableNames() as $variable) {
                $this->populatedVariables[$variable] = null;
            }

            return null;
        }

        $passPhrase = $companion->ask('Enter pass phrase to protect the keys: ');
        $privateKeyPath = $block->getVariable($privateKeyVariableName = $attribute->getVariableNames()[0])->getValue();
        $publicKeyPath = $block->getVariable($publicKeyVariableName = $attribute->getVariableNames()[1])->getValue();

        try {
            (new Process(sprintf('openssl genrsa -out %s -aes256 -passout pass:%s 4096', $privateKeyPath, $passPhrase)))->mustRun();
            (new Process(sprintf('openssl rsa -pubout -in %s -out %s -passin pass:%s', $privateKeyPath, $publicKeyPath, $passPhrase)))->mustRun();
        } catch (\Symfony\Component\Process\Exception\RuntimeException $e) {
            throw new \RuntimeException('Could not have generated the RSA public/private key', $e->getCode(), $e);
        }

        $this->populatedVariables[$privateKeyVariableName] = $privateKeyPath;
        $this->populatedVariables[$publicKeyVariableName] = $publicKeyPath;
        $this->populatedVariables[$attribute->getVariableNames()[2]] = $passPhrase;

        return $this->populatedVariables[$variable->getName()];
    }
});

$application->registerExtension(new class($rootDirectory) implements Extension {
    private $rootDirectory;

    public function __construct(string $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableValue(Companion $companion, Block $block, Variable $variable)
    {
        if (null === ($attribute = $block->getAttribute('file-to-propagate')) || !in_array($variable->getName(), $attribute->getVariableNames())) {
            return null;
        }

        $definedVariablesHash = $companion->getDefinedVariablesHash();

        // If the file exists and seems legit, keep the file.
        if (file_exists($filename = $this->rootDirectory.DIRECTORY_SEPARATOR.$variable->getValue()) && isset($definedVariablesHash[$variable->getName()])) {
            return $definedVariablesHash[$variable->getName()];
        }

        $downloadedFilePath = $companion->ask('<comment>'.$variable->getName().'</comment>: What is the path of your downloaded file? ');
        if (!file_exists($downloadedFilePath)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist', $downloadedFilePath));
        }

        if (false === file_put_contents($filename, file_get_contents($downloadedFilePath))) {
            throw new \RuntimeException(sprintf(
                'Unable to write into "%s"',
                $filename
            ));
        }

        return $variable->getValue();
    }
});
$application->run();
