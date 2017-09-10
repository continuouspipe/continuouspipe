<?php

namespace ContinuousPipe\River\CodeRepository\ImplementationDelegation;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\UuidInterface;

class DelegatesToCodeStreamer implements CodeArchiveStreamer
{
    /**
     * @var CodeArchiveStreamer[]
     */
    private $streamers;

    /**
     * @param CodeArchiveStreamer[] $streamers
     */
    public function __construct(array $streamers = [])
    {
        $this->streamers = $streamers;
    }

    /**
     * {@inheritdoc}
     */
    public function streamCodeArchive(UuidInterface $flowUuid, CodeReference $codeReference): StreamInterface
    {
        foreach ($this->streamers as $streamer) {
            if ($streamer->supports($codeReference)) {
                return $streamer->streamCodeArchive($flowUuid, $codeReference);
            }
        }

        throw new CodeRepositoryException('That code reference has no streamer registered');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CodeReference $codeReference): bool
    {
        foreach ($this->streamers as $streamer) {
            if ($streamer->supports($codeReference)) {
                return true;
            }
        }

        return false;
    }
}
