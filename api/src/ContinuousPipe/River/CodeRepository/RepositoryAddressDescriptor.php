<?php

namespace ContinuousPipe\River\CodeRepository;

class RepositoryAddressDescriptor
{
    const USER_REGEX = '([a-zA-Z0-9-_\.]+)';
    const REPOSITORY_REGEX = self::USER_REGEX;

    public function getDescription($address)
    {
        $patterns = [
            sprintf('#https?://github.com/%s/%s\.git#', self::USER_REGEX, self::REPOSITORY_REGEX),
            sprintf('#https?://github.com/%s/%s#', self::USER_REGEX, self::REPOSITORY_REGEX),
            sprintf('#git@github.com:%s/%s.git#', self::USER_REGEX, self::REPOSITORY_REGEX),
            sprintf('#https://api\.github\.com/repos/%s/%s\GitHubCommitResolver.git#', self::USER_REGEX, self::REPOSITORY_REGEX),
            sprintf('#https://api\.github\.com/repos/%s/%s#', self::USER_REGEX, self::REPOSITORY_REGEX),
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $address, $matches)) {
                return new RepositoryDescription($matches[1], $matches[2]);
            }
        }

        throw new InvalidRepositoryAddress('"%s" is an invalid repository address');
    }
}
