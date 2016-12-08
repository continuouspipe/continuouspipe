<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\View\TideDto;
use ContinuousPipe\River\Infrastructure\Doctrine\Repository\DoctrineFlowRepository;
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
     * @var DoctrineFlowRepository
     */
    private $doctrineFlowRepository;

    /**
     * @param EntityManager  $entityManager
     * @param FlowRepository $doctrineFlowRepository
     */
    public function __construct(EntityManager $entityManager, FlowRepository $doctrineFlowRepository)
    {
        $this->entityManager = $entityManager;
        $this->doctrineFlowRepository = $doctrineFlowRepository;
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
            return $this->dtoToTide($dto);
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
            ->where('dto.flow = :flowUuid')
            ->setParameter('flowUuid', (string) $uuid)
            ->orderBy('dto.tide.creationDate', 'DESC')
        ;

        return new DoctrineTideList($queryBuilder, function (TideDto $dto) {
            return $this->dtoToTide($dto);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findLastByFlowUuid(UuidInterface $flowUuid, $limit)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flowUuid,
        ], [
            'tide.creationDate' => 'DESC',
        ], $limit);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function save(Tide $tide)
    {
        try {
            $dto = $this->findDto($tide->getUuid());
            $dto->merge($tide);
        } catch (TideNotFound $e) {
            $flowDto = $this->doctrineFlowRepository->getDtoByUuid($tide->getFlow()->getUuid());

            $dto = TideDto::fromTide($tide, $flowDto);
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
            'flow' => (string) $flowUuid,
            'tide.codeReference.sha1' => $codeReference->getCommitSha(),
            'tide.codeReference.branch' => $codeReference->getBranch(),
        ]);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findByBranch(Uuid $flowUuid, CodeReference $codeReference)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flowUuid,
            'tide.codeReference.branch' => $codeReference->getBranch(),
        ]);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function find(Uuid $uuid)
    {
        return $this->dtoToTide($this->findDto($uuid));
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuidAndBranch(Uuid $flowUuid, $branch)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flowUuid,
            'tide.codeReference.branch' => $branch,
            'tide.status' => Tide::STATUS_RUNNING,
        ]);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findPendingByFlowUuidAndBranch(Uuid $flowUuid, $branch)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flowUuid,
            'tide.codeReference.branch' => $branch,
            'tide.status' => Tide::STATUS_PENDING,
        ]);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findRunningByFlowUuid(Uuid $flowUuid)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flowUuid,
            'tide.status' => Tide::STATUS_RUNNING,
        ]);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * Get a tide object from its dto.
     *
     * @param TideDto $tideDto
     *
     * @return Tide
     */
    private function dtoToTide(TideDto $tideDto)
    {
        $wrappedTide = $tideDto->getTide();
        $flow = $this->doctrineFlowRepository->flowFromDto($tideDto->getFlow());

        $tide = Tide::create(
            Uuid::fromString($tideDto->getUuid()),
            Flow\Projections\FlatFlow::fromFlow($flow),
            $wrappedTide->getCodeReference(),
            TreeLog::fromId($wrappedTide->getLogId()),
            $wrappedTide->getTeam(),
            $wrappedTide->getUser(),
            $wrappedTide->getConfiguration() ?: [],
            $wrappedTide->getCreationDate()
        );

        $tide->setStatus($wrappedTide->getStatus());
        $tide->setStartDate($wrappedTide->getStartDate());
        $tide->setFinishDate($wrappedTide->getFinishDate());

        return $tide;
    }

    /**
     * @param Uuid $uuid
     *
     * @return TideDto
     *
     * @throws TideNotFound
     */
    private function findDto(Uuid $uuid)
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
