<?php

use Behat\Behat\Context\Context;
use ContinuousPipe\River\CodeRepository;

interface CodeRepositoryContext extends Context
{
    public function thereIsARepositoryIdentified($identifier = null): CodeRepository;
}
