<?php

namespace ContinuousPipe\Pipe\Infrastructure\Doctrine;

use ContinuousPipe\Pipe\DeploymentNotFound;
use ContinuousPipe\Pipe\Infrastructure\Doctrine\Entity\DeploymentViewDto;
use ContinuousPipe\Pipe\View\Deployment;
use ContinuousPipe\Pipe\View\DeploymentRepository;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\UuidInterface;

class DoctrineDeploymentRepository implements DeploymentRepository
{
    const DTO_CLASS = 'ContinuousPipe\Pipe\Infrastructure\Doctrine\Entity\DeploymentViewDto';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find(UuidInterface $uuid)
    {
        return $this->fromDto($this->findDto($uuid));
    }

    /**
     * {@inheritdoc}
     */
    public function save(Deployment $deployment)
    {
        try {
            $dto = $this->findDto($deployment->getUuid());
        } catch (DeploymentNotFound $e) {
            $dto = new DeploymentViewDto();
            $dto->deploymentUuid = $deployment->getUuid();
        }

        $dto->serializedDeploymentView = base64_encode(serialize($deployment));

        $this->entityManager->persist($dto);
        $this->entityManager->flush();

        return $deployment;
    }

    /**
     * @param UuidInterface $uuid
     *
     * @return DeploymentViewDto
     *
     * @throws DeploymentNotFound
     */
    private function findDto(UuidInterface $uuid)
    {
        $dto = $this->getRepository()->findOneBy([
            'deploymentUuid' => (string) $uuid,
        ]);

        if (null === $dto) {
            throw new DeploymentNotFound();
        }

        return $dto;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(self::DTO_CLASS);
    }

    /**
     * @param DeploymentViewDto $dto
     *
     * @return Deployment
     */
    private function fromDto(DeploymentViewDto $dto)
    {
        return unserialize(base64_decode($dto->serializedDeploymentView));
    }
}
