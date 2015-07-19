<?php

namespace ContinuousPipe\Builder\GitHub;

class RepositoryAddressDescriptor
{
    const USER_REGEX = '([a-zA-Z0-9-]+)';
    const REPOSITORY_REGEX = self::USER_REGEX;

    public function getDescription($address)
    {
        $httpPattern = sprintf('#https?://github.com/%s/%s(.git)?#', self::USER_REGEX, self::REPOSITORY_REGEX);
        if (preg_match($httpPattern, $address, $matches)) {
            return new RepositoryDescription($matches[1], $matches[2]);
        }

        $gitPattern = sprintf('#git@github.com:%s/%s.git#', self::USER_REGEX, self::REPOSITORY_REGEX);
        if (preg_match($gitPattern, $address, $matches)) {
            return new RepositoryDescription($matches[1], $matches[2]);
        }

        $apiPattern = sprintf('#https://api\.github\.com/repos/%s/%s#', self::USER_REGEX, self::REPOSITORY_REGEX);
        if (preg_match($apiPattern, $address, $matches)) {
            return new RepositoryDescription($matches[1], $matches[2]);
        }

        throw new InvalidRepositoryAddress();
    }
}
