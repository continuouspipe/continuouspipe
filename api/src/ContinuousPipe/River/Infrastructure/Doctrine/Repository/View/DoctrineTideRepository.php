<?php

namespace ContinuousPipe\River\Infrastructure\Doctrine\Repository\View;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Infrastructure\Doctrine\Entity\View\TideDto;
use ContinuousPipe\River\Infrastructure\Doctrine\Repository\DoctrineFlowRepository;
use ContinuousPipe\River\Repository\TideNotFound;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use ContinuousPipe\Security\User\User;
use Doctrine\ORM\EntityManager;
use LogStream\WrappedLog;
use Rhumsaa\Uuid\Uuid;

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
     * @param EntityManager          $entityManager
     * @param DoctrineFlowRepository $doctrineFlowRepository
     */
    public function __construct(EntityManager $entityManager, DoctrineFlowRepository $doctrineFlowRepository)
    {
        $this->entityManager = $entityManager;
        $this->doctrineFlowRepository = $doctrineFlowRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFlow(Flow $flow)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flow->getUuid(),
        ], [
            'tide.creationDate' => 'DESC',
        ]);

        return array_map(function (TideDto $dto) {
            return $this->dtoToTide($dto);
        }, $dtos);
    }

    /**
     * {@inheritdoc}
     */
    public function findLastByFlow(Flow $flow, $limit)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'flow' => (string) $flow->getUuid(),
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
    public function findByCodeReference(CodeReference $codeReference)
    {
        $dtos = $this->getEntityRepository()->findBy([
            'tide.codeReference.sha1' => $codeReference->getCommitSha(),
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
            \ContinuousPipe\River\View\Flow::fromFlow($flow),
            $wrappedTide->getCodeReference(),
            new WrappedLog($wrappedTide->getLogId()),
            new User($tideDto->getUserEmail()),
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
