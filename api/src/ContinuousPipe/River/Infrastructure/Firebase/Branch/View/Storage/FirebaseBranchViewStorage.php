<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
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
    /**
     * @var FlatFlowRepository
     */
    private $flowRepository;

    public function __construct(
        FirebaseClient $firebaseClient,
        string $databaseUri,
        LoggerInterface $logger,
        BranchQuery $branchQuery,
        SerializerInterface $serializer,
        FlatFlowRepository $flowRepository
    ) {
        $this->databaseUri = $databaseUri;
        $this->logger = $logger;
        $this->firebaseClient = $firebaseClient;
        $this->branchQuery = $branchQuery;
        $this->serializer = $serializer;
        $this->flowRepository = $flowRepository;
    }

    public function save(UuidInterface $flowUuid)
    {
        foreach ($this->branchQuery->findBranches($this->flowRepository->find($flowUuid)) as $branch) {
            try {
                $this->firebaseClient->set(
                    $this->databaseUri,
                    $this->savePath($flowUuid, $branch),
                    $this->saveBody($branch)
                );
            } catch (ApiException $e) {
                $this->logCannotSave($flowUuid, $e);
            }
        }
    }

    public function updateTide(Tide $tide)
    {
        try {
            $this->firebaseClient->update(
                $this->databaseUri,
                $this->tideUpdatePath($tide, $tide->getFlowUuid(), $tide->getCodeReference()->getBranch()),
                $this->normalizeTide($tide)
            );
        } catch (ApiException $e) {
            $this->logCannotUpdate($tide->getFlowUuid(), $e);
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
                $this->savePath($flowUuid, $branch),
                ['pinned' => $pinned]
            );
        } catch (ApiException $e) {
            $this->logCannotUpdate($flowUuid, $e);
        }
    }

    private function logCannotSave(UuidInterface $flowUuid, \Exception $e)
    {
        $this->logger->warning(
            'Unable to save the branch view into Firebase',
            [
                'exception' => $e,
                'message' => $e->getMessage(),
                'flowUuid' => (string) $flowUuid,
            ]
        );
    }

    private function logCannotUpdate(UuidInterface $flowUuid, \Exception $e)
    {
        $this->logger->warning(
            'Unable to update the branches view in Firebase',
            [
                'exception' => $e,
                'message' => $e->getMessage(),
                'flowUuid' => (string) $flowUuid,
            ]
        );
    }

    private function tideUpdatePath(Tide $tide, $flowUuid, $branchName)
    {
        return sprintf(
            'flows/%s/branches/%s/latest-tides/%s',
            (string) $flowUuid,
            $branchName,
            $tide->getUuid()
        );
    }

    private function savePath(UuidInterface $flowUuid, $branch)
    {
        return sprintf('flows/%s/branches/%s', (string) $flowUuid, (string) $branch);
    }

    private function saveBody($branch)
    {
        return [
            'latest-tides' => $this->normalizeTides($branch->getTides()),
            'pinned' => $branch->isPinned(),
        ];
    }

}
