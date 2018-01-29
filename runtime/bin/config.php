<?php
<<<CONFIG
packages:
    - "sroze/companienv: ^0.0.2"
CONFIG;

if (!class_exists('Companienv\Application')) {
    require __DIR__ . '/vendor/autoload.php';
}

$rootDirectory = dirname(dirname(__DIR__));
$application = new \Companienv\Application($rootDirectory);
$application->registerExtension(new class($rootDirectory) implements \Companienv\Extension {
    private $rootDirectory;

    public function __construct(string $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableValue(\Companienv\Companion $companion, \Companienv\DotEnv\Block $block, \Companienv\DotEnv\Variable $variable)
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
