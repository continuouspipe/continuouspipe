<?php

namespace ContinuousPipe\River\Task\Deploy\Configuration\Endpoint;

use ContinuousPipe\DomainName\Transformer;
use ContinuousPipe\River\Flow\Variable\FlowVariableResolver;
use ContinuousPipe\River\Pipeline\TideGenerationException;
use ContinuousPipe\River\Task\Deploy\Configuration\ComponentFactory;
use ContinuousPipe\River\Task\TaskContext;
use ContinuousPipe\River\TideConfigurationException;

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
                $this->resolveHostname($context, $config['host']);
        }

        return $config;
    }

    /**
     * @param string $hostSuffix
     * @return string
     */
    public function generateHostExpression(string $hostSuffix): string
    {
        return sprintf(
            'hash_long_domain_prefix(slugify(code_reference.branch), %s) ~ "%s"',
            ComponentFactory::MAX_HOST_LENGTH - mb_strlen($hostSuffix),
            $hostSuffix
        );
    }

    /**
     * @param TaskContext $context
     * @param array $hostConfiguration
     * @return mixed
     * @throws TideGenerationException
     */
    public function resolveHostname(TaskContext $context, array $hostConfiguration)
    {
        try {
            return $this->flowVariableResolver->resolveExpression(
                $hostConfiguration['expression'],
                $this->flowVariableResolver->createContext($context->getFlowUuid(), $context->getCodeReference())
            );
        } catch (TideConfigurationException $e) {
            throw new TideGenerationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function maxSuffixLength(): int
    {
        return self::MAX_HOST_LENGTH - Transformer::HOST_HASH_LENGTH;
    }
}