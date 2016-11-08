<?php

namespace AppBundle\Controller\Wizard;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\CodeRepository\DockerCompose\ComponentsResolver;
use ContinuousPipe\Security\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/wizard", service="app.controller.wizard.components")
 */
class ComponentsController
{
    /**
     * @var ComponentsResolver
     */
    private $componentsResolver;

    /**
     * @var CodeRepository\CommitResolver
     */
    private $commitResolver;

    /**
     * @param ComponentsResolver $componentsResolver
     */
    public function __construct(ComponentsResolver $componentsResolver, CodeRepository\CommitResolver $commitResolver)
    {
        $this->componentsResolver = $componentsResolver;
        $this->commitResolver = $commitResolver;
    }

    /**
     * @Route("/repositories/{repository}/components/{branch}", methods={"GET"})
     *
     * @ParamConverter("repository", converter="code-repository", options={"identifier"="repository"})
     * @ParamConverter("user", converter="user")
     *
     * @deprecated This should be not be part of the flow creation process, but from the
     *             recommendation later when the flow is configured
     *
     * @View
     */
    public function getAction(CodeRepository $repository, User $user, $branch)
    {
        $sha1 = $this->commitResolver->getLegacyHeadCommitOfBranch($user, $repository, $branch);

        try {
            $components = $this->componentsResolver->resolveByCodeReferenceAndBucket(
                new CodeReference($repository, $sha1, $branch),
                $user
            );
        } catch (CodeRepository\DockerCompose\ResolveException $e) {
            return [];
        }

        return array_map(function (CodeRepository\DockerCompose\DockerComposeComponent $component) {
            return $component->jsonSerialize();
        }, $components);
    }
}
