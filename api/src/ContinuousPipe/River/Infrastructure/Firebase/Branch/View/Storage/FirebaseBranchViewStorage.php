<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\BranchQuery;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Infrastructure\Firebase\FirebaseClient;
use ContinuousPipe\River\View\Storage\BranchViewStorage;
use ContinuousPipe\River\View\Tide;
use Firebase\Exception\ApiException;
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
     * @var FlatFlowRepository
     */
    private $flowRepository;
    /**
     * @var BranchNormalizer
     */
    private $branchNormalizer;

    public function __construct(
        FirebaseClient $firebaseClient,
        string $databaseUri,
        LoggerInterface $logger,
        BranchQuery $branchQuery,
        FlatFlowRepository $flowRepository,
        BranchNormalizer $branchNormalizer
    ) {
        $this->databaseUri = $databaseUri;
        $this->logger = $logger;
        $this->firebaseClient = $firebaseClient;
        $this->branchQuery = $branchQuery;
        $this->flowRepository = $flowRepository;
        $this->branchNormalizer = $branchNormalizer;
    }

    public function save(UuidInterface $flowUuid)
    {
        try {
            $this->firebaseClient->set(
                $this->databaseUri,
                $this->replacePath($flowUuid),
                $this->branchNormalizer->normalizeBranches(
                    $this->branchQuery->findBranches($this->flowRepository->find($flowUuid))
                )
            );
        } catch (ApiException $e) {
            $this->logCannotSave($flowUuid, $e);
        }
    }

    public function updateTide(Tide $tide)
    {
        try {
            $this->firebaseClient->update(
                $this->databaseUri,
                $this->tideUpdatePath($tide, $tide->getFlowUuid(), $tide->getCodeReference()->getBranch()),
                $this->branchNormalizer->normalizeTide($tide)
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
            hash('sha256', $branchName),
            $tide->getUuid()
        );
    }

    private function replacePath(UuidInterface $flowUuid)
    {
        return sprintf('flows/%s/branches', (string) $flowUuid);
    }

    private function savePath(UuidInterface $flowUuid, string $branch)
    {
        return sprintf('flows/%s/branches/%s', (string) $flowUuid, hash('sha256', $branch));
    }

}
