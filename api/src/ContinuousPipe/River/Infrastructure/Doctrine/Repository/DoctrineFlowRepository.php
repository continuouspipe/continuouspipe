<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository;

use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\FlowDto;
use ContinuousPipe\River\Repository\FlowNotFound;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\User\User;
use ContinuousPipe\User\UserRepository;
use Doctrine\ORM\EntityManager;
use Rhumsaa\Uuid\Uuid;

class DoctrineFlowRepository implements FlowRepository
{
    const DTO_CLASS = 'ContinuousPipe\River\Infrastructure\Doctrine\Entity\FlowDto';

    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param EntityManager  $entityManager
     * @param UserRepository $userRepository
     */
    public function __construct(EntityManager $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Flow $flow)
    {
        $flowContext = $flow->getContext();

        try {
            $dto = $this->getDtoByUuid($flowContext->getFlowUuid());
        } catch (FlowNotFound $e) {
            $dto = new FlowDto();
            $dto->uuid = $flow->getUuid();
            $dto->userUsername = $flowContext->getUser()->getEmail();
        }

        $dto->context = $flowContext;
        $dto->tasks = $flow->getTasks();

        $this->entityManager->persist($dto);
        $this->entityManager->flush();

        return $flow;
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        $flowDtos = $this->getEntityRepository()->findBy([
            'userUsername' => $user->getEmail(),
        ]);

        return array_map(function (FlowDto $dto) {
            return $this->flowFromDto($dto);
        }, $flowDtos);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Flow $flow)
    {
        $dto = $this->getDtoByUuid($flow->getUuid());

        $this->entityManager->remove($dto);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        $dto = $this->getDtoByUuid($uuid);

        return $this->flowFromDto($dto);
    }

    /**
     * Get flow from dto.
     *
     * @param FlowDto $dto
     *
     * @return Flow
     */
    public function flowFromDto(FlowDto $dto)
    {
        $flow = new Flow($dto->context, $dto->tasks);

        return $flow;
    }

    /**
     * @param Uuid $uuid
     * @return FlowDto
     * @throws FlowNotFound
     */
    public function getDtoByUuid(Uuid $uuid)
    {
        $dto = $this->getEntityRepository()->find((string) $uuid);
        if (null === $dto) {
            throw new FlowNotFound();
        }

        return $dto;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getEntityRepository()
    {
        return $this->entityManager->getRepository(self::DTO_CLASS);
    }
}
