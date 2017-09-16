<?php

namespace ContinuousPipe\River\Flex\AsFeature\CommandHandler;

use ContinuousPipe\Events\Transaction\TransactionManager;
use ContinuousPipe\River\Flex\AsFeature\Command\ActivateFlex;
use ContinuousPipe\River\Flex\Resources\DockerRegistry\DockerRegistryManager;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\Security\Authenticator\AuthenticatorClient;
use ContinuousPipe\Security\Credentials\BucketRepository;

class DoActivateFlex
{
    /**
     * @var FlowRepository
     */
    private $flowRepository;

    /**
     * @var AuthenticatorClient
     */
    private $authenticatorClient;

    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var TransactionManager
     */
    private $flowTransactionManager;

    /**
     * @var DockerRegistryManager
     */
    private $dockerRegistryManager;

    public function __construct(
        FlowRepository $flowRepository,
        AuthenticatorClient $authenticatorClient,
        TransactionManager $flowTransactionManager,
        BucketRepository $bucketRepository,
        DockerRegistryManager $dockerRegistryManager
    ) {
        $this->flowRepository = $flowRepository;
        $this->bucketRepository = $bucketRepository;
        $this->flowTransactionManager = $flowTransactionManager;
        $this->authenticatorClient = $authenticatorClient;
        $this->dockerRegistryManager = $dockerRegistryManager;
    }

    public function handle(ActivateFlex $command)
    {
        $flow = $this->flowRepository->find($command->getFlowUuid());

        $this->dockerRegistryManager->createRepositoryForFlow($flow);
        $this->flowTransactionManager->apply($flow->getUuid()->toString(), function (Flow $flow) {
            $flow->activateFlex();
        });
    }
}
