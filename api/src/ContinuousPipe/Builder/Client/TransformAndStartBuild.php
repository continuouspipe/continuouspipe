<?php

namespace ContinuousPipe\Builder\Client;

use ContinuousPipe\Builder\Aggregate\BuildFactory;
use ContinuousPipe\Builder\Aggregate\Command\StartBuild;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Builder\View\BuildViewRepository;
use ContinuousPipe\Security\User\User;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransformAndStartBuild implements BuilderClient
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var BuildFactory
     */
    private $buildFactory;

    /**
     * @var BuildViewRepository
     */
    private $buildViewRepository;
    /**
     * @var BuildRequestTransformer
     */
    private $buildRequestTransformer;
    /**
     * @var MessageBus
     */
    private $commandBus;

    public function __construct(
        MessageBus $commandBus,
        ValidatorInterface $validator,
        BuildFactory $buildFactory,
        BuildViewRepository $buildViewRepository,
        BuildRequestTransformer $buildRequestTransformer
    ) {
        $this->commandBus = $commandBus;
        $this->validator = $validator;
        $this->buildFactory = $buildFactory;
        $this->buildViewRepository = $buildViewRepository;
        $this->buildRequestTransformer = $buildRequestTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildRequest $buildRequest): Build
    {
        $violations = $this->validator->validate($buildRequest);
        if ($violations->count() > 0) {
            throw new BuilderException($violations->get(0)->getMessage());
        }

        try {
            $build = $this->buildFactory->fromRequest(
                $this->buildRequestTransformer->transform($buildRequest)
            );
        } catch (BuildRequestException $e) {
            throw new BuilderException($e->getMessage(), $e->getCode(), $e);
        }

        $this->commandBus->handle(new StartBuild($build->getIdentifier()));

        $view = $this->buildViewRepository->find($build->getIdentifier());

        return $view;
    }
}
