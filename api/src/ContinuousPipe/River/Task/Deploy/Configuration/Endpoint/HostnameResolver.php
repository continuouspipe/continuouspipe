<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\DomainName\Transformer;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\Deploy\Configuration\ComponentFactory;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\TideConfigurationException;
use Ramsey\Uuid\UuidInterface;

class HostnameResolver
{
    const MAX_HOST_LENGTH = 64;

    /**
     * @var FlowVariableResolver
     */
    private $flowVariableResolver;

    public function __construct(FlowVariableResolver $flowVariableResolver)
    {
        $this->flowVariableResolver = $flowVariableResolver;
    }

    public function checkSuffixLength(string $configKey, array $endpointConfiguration)
    {
        if ($this->suffixTooLong($endpointConfiguration[$configKey]['record_suffix'])) {
            throw new TideGenerationException(
                sprintf(
                    'The %s record_suffix cannot be more than %s characters long',
                    $configKey,
                    $this->maxSuffixLength()
                )
            );
        }
    }

    public function suffixTooLong(string $suffix): bool
    {
        return mb_strlen($suffix) > $this->maxSuffixLength();
    }

    public function addHost(array $config, TaskContext $context, string $hostKey)
    {
        if (isset($config['record_suffix'])) {
            $config['host']['expression'] =
                $this->generateHostExpression($config['record_suffix']);
        }
        if (isset($config['host'])) {
            $config[$hostKey] =
                $this->resolveHostname($context->getFlowUuid(), $context->getCodeReference(), $config['host']['expression']);
        }

        return $config;
    }

    /**
     * @param string $hostSuffix
     *
     * @throws TideGenerationException
     *
     * @return string
     */
    public function generateHostExpression(string $hostSuffix): string
    {
        if ($this->suffixTooLong($hostSuffix)) {
            throw new TideGenerationException(
                sprintf(
                    'The ingress host_suffix cannot be more than %s characters long',
                    $this->maxSuffixLength()
                )
            );
        }

        return sprintf(
            'hash_long_domain_prefix(slugify(code_reference.branch), %s) ~ "%s"',
            ComponentFactory::MAX_HOST_LENGTH - mb_strlen($hostSuffix),
            $hostSuffix
        );
    }

    /**
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     * @param string        $expression
     *
     * @throws TideGenerationException
     *
     * @return string
     */
    public function resolveHostname(UuidInterface $flowUuid, CodeReference $codeReference, string $expression) : string
    {
        try {
            $hostname = $this->flowVariableResolver->resolveExpression(
                $expression,
                $this->flowVariableResolver->createContext($flowUuid, $codeReference)
            );
        } catch (TideConfigurationException $e) {
            throw new TideGenerationException($e->getMessage(), $e->getCode(), $e);
        }

        if (!is_string($hostname)) {
            throw new TideGenerationException('Generate hostname is not a string: '.$hostname);
        }

        return $hostname;
    }

    public function maxSuffixLength(): int
    {
        return self::MAX_HOST_LENGTH - Transformer::HOST_HASH_LENGTH;
    }
}
