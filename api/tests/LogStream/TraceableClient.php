<?php

namespace LogStream;

class TraceableClient implements Client
{
    /**
     * @var Client
     */
    private $decoratedClient;

    /**
     * @var Log[]
     */
    private $archived;

    /**
     * @var Log[]
     */
    private $created = [];

    /**
     * @var Log[]
     */
    private $patched;

    /**
     * @param Client $decoratedClient
     */
    public function __construct(Client $decoratedClient)
    {
        $this->decoratedClient = $decoratedClient;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Log $log)
    {
        $created = $this->decoratedClient->create($log);

        $this->created[] = $created;

        return $created;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus(Log $log, $status)
    {
        return $this->decoratedClient->updateStatus($log, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(Log $log, array $patch)
    {
        $patched = $this->decoratedClient->patch($log, $patch);

        $this->patched[] = $patched;

        return $patched;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(Log $log)
    {
        $archived = $this->decoratedClient->archive($log);

        $this->archived[] = $archived;

        return $archived;
    }

    /**
     * @return Log[]
     */
    public function getArchived(): array
    {
        return $this->archived;
    }

    /**
     * @return Log[]
     */
    public function getCreated(): array
    {
        return $this->created;
    }

    /**
     * @return Log[]
     */
    public function getPatched(): array
    {
        return $this->patched;
    }
}
