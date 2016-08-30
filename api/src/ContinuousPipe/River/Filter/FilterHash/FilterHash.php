<?php

namespace ContinuousPipe\River\Filter\FilterHash;

use Ramsey\Uuid\UuidInterface;

class FilterHash
{
    /**
     * @var UuidInterface
     */
    private $tideUuid;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param UuidInterface $tideUuid
     * @param string        $hash
     */
    public function __construct(UuidInterface $tideUuid, $hash)
    {
        $this->tideUuid = $tideUuid;
        $this->hash = $hash;
    }

    /**
     * @return UuidInterface
     */
    public function getTideUuid()
    {
        return $this->tideUuid;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
