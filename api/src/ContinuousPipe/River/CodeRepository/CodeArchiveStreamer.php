<?php

namespace ContinuousPipe\River\CodeRepository;

use ContinuousPipe\River\CodeReference;
use Psr\Http\Message\StreamInterface;
use Ramsey\Uuid\UuidInterface;

interface CodeArchiveStreamer
{
    /**
     * Stream the code archive of the given code reference for this flow.
     *
     * The stream needs to be a application/gzip content type.
     *
     * @param UuidInterface $flowUuid
     * @param CodeReference $codeReference
     *
     * @throws CodeRepositoryException
     *
     * @return StreamInterface
     */
    public function streamCodeArchive(UuidInterface $flowUuid, CodeReference $codeReference) : StreamInterface;

    /**
     * Returns true if the streamer supports the following code repository.
     *
     * @param CodeReference $codeReference
     *
     * @return bool
     */
    public function supports(CodeReference $codeReference) : bool;
}
