<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\View\TideDto;
use ContinuousPipe\River\Repository\FlowRepository;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Doctrine\ORM\EntityManager;
use LogStream\Tree\TreeLog;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrineTideRepository implements TideRepository
{
    const DTO_CLASS = 'ContinuousPipe\River\Infrastructure\Doctrine\Entity\View\TideDto';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var FlatFlowRepository
     */
    private $flowRepository;

    /**
     * @param EntityManager      $entityManager
     * @param FlatFlowRepository $flowRepository
     */
    public function __construct(EntityManager $entityManager, FlatFlowRepository $flowRepository)
    {
        $this->entityManager = $entityManager;
        $this->flowRepository = $flowRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $queryBuilder = $this
            ->getEntityRepository()
            ->createQueryBuilder('dto')
            ->orderBy('dto.tide.creationDate', 'DESC')
        ;

        return new DoctrineTideList($queryBuilder, function (TideDto $dto) {
            return $dto->toTide();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlowUuid(Uuid $uuid)
    {
        $queryBuilder = $this
            ->getEntityRepository()
            ->createQueryBuilder('dto')
            ->where('dto.flowUuid = :flowUuid')
            ->setParameter('flowUuid', (string) $uuid)
            ->orderBy('dto.tide.creationDate', 'DESC')
        ;

        return new DoctrineTideList($queryBuilder, function (TideDto $dto) {
            return $dto->toTide();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findLastByFlowUuid(UuidInterface $flowUuid, $limit)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
        ], [
            'tide.creationDate' => 'DESC',
        ], $limit);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Tide $tide)
    {
        $pipeline = $tide->getPipeline() !== null ? $this->entityManager->merge($tide->getPipeline()) : null;

        try {
            $dto = $this->findDto($tide->getUuid());
            $dto->merge($tide, $pipeline);
        } catch (TideNotFound $e) {
            $dto = TideDto::fromTide($tide, $pipeline);
        }

        $this->entityManager->persist($dto);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function findByCodeReference(Uuid $flowUuid, CodeReference $codeReference)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.codeReference.sha1' => $codeReference->getCommitSha(),
            'tide.codeReference.branch' => $codeReference->getBranch(),
        ]);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findByBranch(Uuid $flowUuid, $branch)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.codeReference.branch' => $branch,
        ], [
            'tide.creationDate' => 'DESC',
        ]);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        return $this->findDto($uuid)->toTide();
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuidAndBranch(Uuid $flowUuid, $branch)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.codeReference.branch' => $branch,
            'tide.status' => Tide::STATUS_RUNNING,
        ]);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingByFlowUuidAndBranch(Uuid $flowUuid, $branch)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.codeReference.branch' => $branch,
            'tide.status' => Tide::STATUS_PENDING,
        ]);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findLastSuccessfulByFlowUuidAndBranch(UuidInterface $flowUuid, string $branch, int $limit) : array
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.codeReference.branch' => $branch,
            'tide.status' => Tide::STATUS_SUCCESS,
        ], [
            'tide.creationDate' => 'DESC',
        ], $limit);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuid(Uuid $flowUuid)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.status' => Tide::STATUS_RUNNING,
        ]);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findByGenerationUuid(UuidInterface $flowUuid, UuidInterface $generationUuid)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flowUuid' => (string) $flowUuid,
            'tide.generationUuid' => $generationUuid,
        ]);

        return array_map(function (TideDto $dto) {
            return $dto->toTide();
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function countStartedTidesByFlowSince(UuidInterface $flowUuid, \DateTime $from): int
    {
        $qb = $this->getEntityRepository()->createQueryBuilder('e');
        $qb->select('COUNT(e.uuid)');
        $qb->where('e.flowUuid = :uuid');
        $qb->andWhere('e.tide.startDate > :startDate');
        $query = $qb->getQuery();
        $query->setParameters(['uuid' => $flowUuid->toString(), 'startDate' => $from->format('Y-m-d')]);
        return (int) $query->getSingleScalarResult();
    }


    /**
     * @param UuidInterface $uuid
     *
     * @return TideDto
     *
     * @throws TideNotFound
     */
    private function findDto(UuidInterface $uuid)
    {
        if (null == ($dto = $this->getEntityRepository()->find((string) $uuid))) {
            throw new TideNotFound();
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
