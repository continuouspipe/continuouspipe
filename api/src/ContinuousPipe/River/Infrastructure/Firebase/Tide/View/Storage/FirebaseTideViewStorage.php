<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Tide\View\Storage;

use ContinuousPipe\River\Infrastructure\Firebase\DatabaseFactory;
use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use ContinuousPipe\River\View\Storage\TideViewStorage;
use ContinuousPipe\River\View\Tide;
use Firebase\Exception\ApiException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;

class FirebaseTideViewStorage implements TideViewStorage
{
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
     * @var FirebaseClient
     */
    private $firebaseClient;

    public function __construct(FirebaseClient $firebaseClient, SerializerInterface $serializer, LoggerInterface $logger, string $databaseUri)
    {
        $this->databaseUri = $databaseUri;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->firebaseClient = $firebaseClient;
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

        try {
            $this->firebaseClient->set(
                $this->databaseUri,
                sprintf(
                    'flows/%s/tides/by-pipelines/%s/%s',
                    (string) $tide->getFlowUuid(),
                    (string) $pipeline->getUuid(),
                    (string) $tide->getUuid()
                ),
                $this->normalizeTide($tide)
            );

            // Updates the pipelines' view
            $this->firebaseClient->update(
                $this->databaseUri,
                    sprintf(
                    'flows/%s/pipelines/%s',
                    (string) $tide->getFlowUuid(),
                    (string) $pipeline->getUuid()
                ), [
                    'uuid' => (string) $pipeline->getUuid(),
                    'name' => $pipeline->getName(),
                ]
            );
        } catch (ApiException $e) {
            $this->logger->warning('Unable to save the tide view into Firebase', [
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
