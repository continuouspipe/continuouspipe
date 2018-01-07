<?php

namespace ContinuousPipe\Security\Request\ParamConverter;

use ContinuousPipe\Security\Team\TeamNotFound;
use ContinuousPipe\Security\Team\TeamRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamParamConverter implements ParamConverterInterface
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @var string
     */
    private $converterName;

    public function __construct(TeamRepository $teamRepository, string $converterName = 'team')
    {
        $this->teamRepository = $teamRepository;
        $this->converterName = $converterName;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = array_merge(['slug' => 'slug'], $configuration->getOptions());
        if (null === ($slug = $request->get($options['slug']))) {
            throw new HttpException(500, sprintf('No slug found in field "%s"', $options['slug']));
        }

        try {
            $team = $this->teamRepository->find($slug);
        } catch (TeamNotFound $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        $request->attributes->set($configuration->getName(), $team);

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
