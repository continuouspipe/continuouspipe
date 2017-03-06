<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\CodeRepository;

interface CodeRepositoryContext extends Context
{
    public function thereIsARepositoryIdentified($identifier = null): CodeRepository;

    public function thereIsAFileContaining(string $path, string $contents);

    public function aFileDoesNotExists(string $path);
}
