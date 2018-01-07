<?php

namespace ContinuousPipe\Security\Request\ParamConverter;

use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BucketParamConverter implements ParamConverterInterface
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @var string
     */
    private $converterName;

    public function __construct(BucketRepository $bucketRepository, string $converterName = 'bucket')
    {
        $this->bucketRepository = $bucketRepository;
        $this->converterName = $converterName;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = array_merge(['uuid' => 'uuid'], $configuration->getOptions());
        if (null === ($uuid = $request->get($options['uuid']))) {
            throw new HttpException(500, sprintf('No uuid found in field "%s"', $options['uuid']));
        }

        try {
            $bucket = $this->bucketRepository->find(Uuid::fromString($uuid));
        } catch (BucketNotFound $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $request->attributes->set($configuration->getName(), $bucket);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == $this->converterName;
    }
}
