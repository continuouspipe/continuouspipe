<?php

namespace ContinuousPipe\River\CodeRepository\WebHook;

use ContinuousPipe\River\CodeRepository;
use GitHub\WebHook\Setup\WebHookManager;

class RepositoryWebHookManager
{
    /**
     * @var WebHookManager
     */
    private $webHookManager;

    /**
     * @param WebHookManager $webHookManager
     */
    public function __construct(WebHookManager $webHookManager)
    {
        $this->webHookManager = $webHookManager;
    }

    /**
     * @param CodeRepository $codeRepository
     */
    public function configureWebHookForRepository(CodeRepository $codeRepository)
    {
        if ($codeRepository instanceof CodeRepository\GitHub\GitHubCodeRepository) {
            $gitHubRepository = $codeRepository->getGitHubRepository();

            $this->webHookManager->setup($gitHubRepository);
        } else {
            throw new \RuntimeException('');
        }
    }
}
