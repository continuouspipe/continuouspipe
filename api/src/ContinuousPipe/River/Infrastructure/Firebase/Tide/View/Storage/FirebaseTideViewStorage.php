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
        $referencePath = sprintf('flows/%s/tides/%s', (string) $tide->getFlowUuid(), (string) $tide->getUuid());

        $database = $this->databaseFactory->create($this->databaseUri);

        try {
            $context = SerializationContext::create();
            $context->setGroups(['Default']);

            $database->getReference($referencePath)->set(
                \GuzzleHttp\json_decode($this->serializer->serialize($tide, 'json', $context))
            );
        } catch (ApiException $e) {
            $this->logger->error('Unable to save the tide view into Firebase', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'tideUuid' => (string) $tide->getUuid(),
            ]);
        }
    }
}
