<?php

namespace ContinuousPipe\DomainName;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class DomainNameExpressions implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'hash_long_domain_prefix',
                function () {
                    throw new \RuntimeException('This function is not compilable');
                },
                function (array $context, string $hostPrefix, int $maxLength) {
                    return (new Transformer())->shortenWithHash($hostPrefix, $maxLength);
                }
            ),
            new ExpressionFunction(
                'slugify',
                function () {
                    throw new \RuntimeException('This function is not compilable');
                },
                function (array $context, string $hostname) {
                    return (new Transformer())->slugify($hostname);
                }
            )
        ];
    }
}
