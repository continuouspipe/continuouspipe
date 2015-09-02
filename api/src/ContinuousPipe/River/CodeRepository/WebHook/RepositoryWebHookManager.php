<?php

namespace ContinuousPipe\River\CodeRepository\WebHook;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use GitHub\WebHook\Model\WebHook;
use GitHub\WebHook\Model\WebHookConfiguration;
use GitHub\WebHook\Setup\WebHookManager;
use GuzzleHttp\Exception\BadResponseException;
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
     * @var string
     */
    private $riverPublicUrl;

    /**
     * @param WebHookManager        $webHookManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param string                $githubSecret
     * @param string                $riverPublicUrl
     */
    public function __construct(WebHookManager $webHookManager, UrlGeneratorInterface $urlGenerator, $githubSecret, $riverPublicUrl)
    {
        $this->webHookManager = $webHookManager;
        $this->urlGenerator = $urlGenerator;
        $this->githubSecret = $githubSecret;
        $this->riverPublicUrl = $riverPublicUrl;
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

        $targetUrl = $this->getBaseUrl().$this->urlGenerator->generate('web_hook_github', ['uuid' => (string) $flow->getUuid()]);
        $configuration = new WebHookConfiguration($targetUrl, 'json', $this->githubSecret);
        $webHook = new WebHook('web', $configuration, [
            'pull_request',
            'push',
        ]);

        try {
            $this->webHookManager->setup($codeRepository->getGitHubRepository(), $webHook);
        } catch (BadResponseException $e) {
            throw new CouldNotCreateWebHookException('Could not create GitHub web hook for the repository.');
        }
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $baseUrl = $this->riverPublicUrl;

        if (strpos($baseUrl, 'http') !== 0) {
            $baseUrl = 'http://'.$baseUrl;
        }

        return $baseUrl;
    }
}
