<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine;

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
        $dto = $this->getDtoByUuid($flow->getUuid());
        if (null === $dto) {
            $dto = new FlowDto();
            $dto->uuid = $flow->getUuid();
        }

        $dto->codeRepositoryIdentifier = $flow->getRepository()->getIdentifier();
        $dto->codeRepository = $flow->getRepository();
        $dto->userUsername = $flow->getUser()->getEmail();

        $this->entityManager->persist($dto);
        $this->entityManager->flush();

        return $flow;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByRepositoryIdentifier($identifier)
    {
        $repository = $this->getEntityRepository();
        $flowDto = $repository->findOneBy([
            'codeRepositoryIdentifier' => $identifier,
        ]);

        if (null === $flowDto) {
            throw new FlowNotFound();
        }

        return $this->flowFromDto($flowDto);
    }

    /**
     * {@inheritdoc}
     */
    public function findByUser(User $user)
    {
        $flowDtos = $this->getEntityRepository()->findBy([
            'userUsername' => $user->getEmail()
        ]);

        return array_map(function(FlowDto $dto) {
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
    private function flowFromDto(FlowDto $dto)
    {
        $user = $this->userRepository->findOneByEmail($dto->userUsername);
        $flow = new Flow(Uuid::fromString($dto->uuid), $user, $dto->codeRepository);

        return $flow;
    }

    /**
     * @param Uuid $uuid
     *
     * @return null|FlowDto
     */
    private function getDtoByUuid(Uuid $uuid)
    {
        return $this->getEntityRepository()->find((string) $uuid);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getEntityRepository()
    {
        return $this->entityManager->getRepository(self::DTO_CLASS);
    }
}
