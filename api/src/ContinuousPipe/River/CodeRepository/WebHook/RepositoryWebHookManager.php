<?php

namespace ContinuousPipe\River\CodeRepository\WebHook;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use GitHub\WebHook\Model\WebHook;
use GitHub\WebHook\Model\WebHookConfiguration;
use GitHub\WebHook\Setup\WebHookManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RepositoryWebHookManager
{
    /**
     * @var WebHookManager
     */
    private $webHookManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $githubSecret;

    /**
     * @param WebHookManager        $webHookManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $githubSecret
     */
    public function __construct(WebHookManager $webHookManager, UrlGeneratorInterface $urlGenerator, $githubSecret)
    {
        $this->webHookManager = $webHookManager;
        $this->urlGenerator = $urlGenerator;
        $this->githubSecret = $githubSecret;
    }

    /**
     * @param Flow $flow
     */
    public function configureWebHookForFlow(Flow $flow)
    {
        $codeRepository = $flow->getContext()->getCodeRepository();
        if (!$codeRepository instanceof CodeRepository\GitHub\GitHubCodeRepository) {
            throw new \RuntimeException(sprintf(
                'Repository of type "%s" not supported for webhook configuration',
                get_class($codeRepository)
            ));
        }

        $targetUrl = $this->urlGenerator->generate('web_hook_github', ['uuid' => (string) $flow->getUuid()]);
        $configuration = new WebHookConfiguration($targetUrl, 'json', $this->githubSecret);
        $webHook = new WebHook('web', $configuration, [
            'pull_request',
            'push',
        ]);

        $this->webHookManager->setup($codeRepository->getGitHubRepository(), $webHook);
    }
}
