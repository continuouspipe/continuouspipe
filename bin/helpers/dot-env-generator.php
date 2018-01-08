<?php
<<<CONFIG
packages:
    - "symfony/console: ~4.0"
CONFIG;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

$rootFolder = __DIR__.'/../../';
$defaultVariables = get_variables_from_env_file($rootFolder.'.env.dist');
$definedVariables = file_exists($rootFolder.'.env') ? get_variables_from_env_file($rootFolder.'.env') : [];
$missingVariables = [];

foreach ($defaultVariables as $name => $value) {
    if (!isset($defaultVariables[$name])) {
        $missingVariables[$name] = $value;
    }
}

$output = new ConsoleOutput();
$table = new Table($output);
$table->setHeaders(['Configuration', 'Value']);
$rows = [];
foreach ($defaultVariables as $name => $value) {
    $displayValue = isset($definedVariables[$name])
        ? $definedVariables[$name] == $value ? '<info>Default value</info> ('.$value.')'  : $definedVariables[$name]
        : '<error>Missing</error>';

    $rows[] = [
        $name,
        $displayValue
    ];
}

$table->setRows($rows);
$table->render();

function get_variables_from_env_file($path)
{
    $variables = [];

    foreach (file($path) as $number => $line) {
        if (empty(trim($line)) || strpos($line, '#') === 0) {
            continue;
        }

        $sides = explode('=', $line);
        if (count($sides) != 2) {
            throw new \InvalidArgumentException(sprintf(
                'The line %d of the file %s is invalid: %s',
                $number,
                $path,
                $line
            ));
        }
        $variables[$sides[0]] = $sides[1];
    }

    return $variables;
}
