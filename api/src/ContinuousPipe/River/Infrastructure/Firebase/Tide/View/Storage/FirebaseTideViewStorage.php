<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Tide\View\Storage;

use ContinuousPipe\River\Infrastructure\Firebase\DatabaseFactory;
use ContinuousPipe\River\View\Storage\TideViewStorage;
use ContinuousPipe\River\View\Tide;
use Firebase\Exception\ApiException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

class FirebaseTideViewStorage implements TideViewStorage
{
    /**
     * @var DatabaseFactory
     */
    private $databaseFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $databaseUri;

    /**
     * @param DatabaseFactory     $databaseFactory
     * @param SerializerInterface $serializer
     * @param LoggerInterface     $logger
     * @param string              $databaseUri
     */
    public function __construct(DatabaseFactory $databaseFactory, SerializerInterface $serializer, LoggerInterface $logger, string $databaseUri)
    {
        $this->databaseFactory = $databaseFactory;
        $this->databaseUri = $databaseUri;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Tide $tide)
    {
        if (null === ($pipeline = $tide->getPipeline())) {
            $this->logger->error('Attempted to save a tide view without any pipeline', [
                'tide_uuid' => (string) $tide->getUuid(),
                'flow_uuid' => (string) $tide->getFlowUuid(),
            ]);

            return;
        }

        $database = $this->databaseFactory->create($this->databaseUri);

        try {
            // Update the tides by pipelines view
            $database->getReference(sprintf(
                'flows/%s/tides/by-pipelines/%s/%s',
                (string) $tide->getFlowUuid(),
                (string) $pipeline->getUuid(),
                (string) $tide->getUuid()
            ))->set($this->normalizeTide($tide));

            // Updates the pipelines' view
            $database->getReference(sprintf(
                'flows/%s/pipelines/%s',
                (string) $tide->getFlowUuid(),
                (string) $pipeline->getUuid()
            ))->update([
                'uuid' => (string) $pipeline->getUuid(),
                'name' => $pipeline->getName(),
            ]);
        } catch (ApiException $e) {
            $this->logger->error('Unable to save the tide view into Firebase', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'tideUuid' => (string) $tide->getUuid(),
            ]);
        }
    }

    /**
     * @param Tide $tide
     *
     * @return array
     */
    private function normalizeTide(Tide $tide): array
    {
        $context = SerializationContext::create();
        $context->setGroups(['Default']);

        return \GuzzleHttp\json_decode($this->serializer->serialize($tide, 'json', $context), true);
    }
}
