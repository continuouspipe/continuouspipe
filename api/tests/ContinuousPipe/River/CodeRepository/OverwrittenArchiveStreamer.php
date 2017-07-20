<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\UuidInterface;

class OverwrittenArchiveStreamer implements CodeArchiveStreamer
{
    /**
     * @var CodeArchiveStreamer
     */
    private $streamer;

    /**
     * @var array
     */
    private $overwrittenFlows = [];

    /**
     * @param CodeArchiveStreamer $streamer
     */
    public function __construct(CodeArchiveStreamer $streamer)
    {
        $this->streamer = $streamer;
    }

    /**
     * {@inheritdoc}
     */
    public function streamCodeArchive(UuidInterface $flowUuid, CodeReference $codeReference): StreamInterface
    {
        foreach ($this->overwrittenFlows as $overwrittenFlowUuid => $streamFactory) {
            if ($flowUuid->toString() === $overwrittenFlowUuid) {
                return $streamFactory();
            }
        }

        return $this->streamer->streamCodeArchive($flowUuid, $codeReference);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CodeReference $codeReference): bool
    {
        return $this->streamer->supports($codeReference);
    }

    public function overwriteForFlow(string $flowUuid, callable $streamFactory)
    {
        $this->overwrittenFlows[$flowUuid] = $streamFactory;
    }
}
