<?php

namespace ContinuousPipe\Builder\Docker\HttpClient;

use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\Exception\DaemonNetworkException;
use ContinuousPipe\Builder\Docker\Exception\PushAlreadyInProgress;

class ExceptionResolverHandler implements OutputHandler
{
    /**
     * @var OutputHandler
     */
    private $outputHandler;

    /**
     * @var array
     */
    private $matchRules = [
        '/^Head ([a-z0-9:\/\.-]+): EOF$/' => DaemonNetworkException::class,
        '/^Head ([a-z0-9:\/\.-]+): ([a-z0-9\ \/:\.]+): i\/o timeout$/' => DaemonNetworkException::class,
        '/^use of closed network connection$/' => DaemonNetworkException::class,
        '/^push (or pull )?([a-z0-9\.\/]+) is already in progress$/' => PushAlreadyInProgress::class,
        '/net\/http: TLS handshake timeout$/' => DaemonNetworkException::class,
    ];

    /**
     * @param OutputHandler $outputHandler
     */
    public function __construct(OutputHandler $outputHandler)
    {
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($output)
    {
        try {
            return $this->outputHandler->handle($output);
        } catch (DockerException $e) {
            $message = trim($e->getMessage());

            foreach ($this->matchRules as $regex => $exceptionClass) {
                if (preg_match($regex, $message)) {
                    throw new $exceptionClass($e->getMessage(), $e->getCode(), $e);
                }
            }

            throw $e;
        }
    }
}
