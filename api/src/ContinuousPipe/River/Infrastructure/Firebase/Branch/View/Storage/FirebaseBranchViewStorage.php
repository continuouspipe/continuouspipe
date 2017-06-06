<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use ContinuousPipe\River\View\Storage\BranchViewStorage;
use ContinuousPipe\River\View\Tide;
use Firebase\Exception\ApiException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;

class FirebaseBranchViewStorage implements BranchViewStorage
{
    /**
     * @var string
     */
    private $databaseUri;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FirebaseClient
     */
    private $firebaseClient;
    /**
     * @var BranchQuery
     */
    private $branchQuery;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        FirebaseClient $firebaseClient,
        string $databaseUri,
        LoggerInterface $logger,
        BranchQuery $branchQuery,
        SerializerInterface $serializer
    ) {
        $this->databaseUri = $databaseUri;
        $this->logger = $logger;
        $this->firebaseClient = $firebaseClient;
        $this->branchQuery = $branchQuery;
        $this->serializer = $serializer;
    }

    public function save(UuidInterface $flowUuid)
    {
        $branches = $this->branchQuery->findBranches($flowUuid);

        foreach ($branches as $branch) {
            try {
                $this->firebaseClient->set(
                    $this->databaseUri,
                    sprintf(
                        'flows/%s/branches/%s/latest-tides',
                        (string) $flowUuid,
                        (string) $branch
                    ),
                    $this->normalizeTides($branch->getTides())
                );
            } catch (ApiException $e) {
                $this->logger->warning(
                    'Unable to save the branch view into Firebase',
                    [
                        'exception' => $e,
                        'message' => $e->getMessage(),
                        'flowUuid' => (string) $flowUuid,
                    ]
                );
            }
        }
    }

    public function updateTide(Tide $tide)
    {
        //TODO do the full branch update if it's not already there????
        $flowUuid = $tide->getFlowUuid();
        $branchName = $tide->getCodeReference()->getBranch();

        try {
            $this->firebaseClient->update(
                $this->databaseUri,
                sprintf(
                    'flows/%s/branches/%s/latest-tides/%s',
                    (string) $flowUuid,
                    $branchName,
                    $tide->getUuid()
                ),
                $this->normalizeTide($tide)
            );
        } catch (ApiException $e) {
            $this->logger->warning(
                'Unable to update the branches view in Firebase',
                [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                    'flowUuid' => (string) $flowUuid,
                ]
            );
        }
    }

    public function branchPinned(UuidInterface $flowUuid, string $branch)
    {
        $this->updateBranchPinning($flowUuid, $branch, true);
    }

    public function branchUnpinned(UuidInterface $flowUuid, string $branch)
    {
        $this->updateBranchPinning($flowUuid, $branch, false);
    }

    private function normalizeTides(array $tides): array
    {
        return array_combine(
            array_map(
                function (Tide $tide) {
                    return $tide->getUuid();
                },
                $tides
            ),
            array_map([$this, 'normalizeTide'], $tides)
        );
    }

    private function normalizeTide(Tide $tide): array
    {
        $context = SerializationContext::create();
        $context->setGroups(['Default']);

        return \GuzzleHttp\json_decode($this->serializer->serialize($tide, 'json', $context), true);
    }

    private function updateBranchPinning(UuidInterface $flowUuid, string $branch, $pinned)
    {
        try {
            $this->firebaseClient->update(
                $this->databaseUri,
                sprintf(
                    'flows/%s/branches/%s',
                    (string) $flowUuid,
                    $branch
                ),
                ['pinned' => $pinned]
            );
        } catch (ApiException $e) {
            $this->logger->warning(
                'Unable to update the branches view in Firebase',
                [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                    'flowUuid' => (string) $flowUuid,
                ]
            );
        }
    }
}
