<?php

namespace ContinuousPipe\River\Filter\CodeChanges;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\ChangesComparator;
use ContinuousPipe\River\Flow\Projections\FlatFlowRepository;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\View\TideRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Finder\Glob;

class CompareWithLastTideCodeChangesResolver implements CodeChangesResolver
{
    /**
     * @var TideRepository
     */

    private $tideRepository;

    /**
     * @var FlatFlowRepository
     */
    private $flatFlowRepository;

    /**
     * @var ChangesComparator
     */
    private $changesComparator;

    public function __construct(TideRepository $tideRepository, FlatFlowRepository $flatFlowRepository, ChangesComparator $changesComparator)
    {
        $this->tideRepository = $tideRepository;
        $this->flatFlowRepository = $flatFlowRepository;
        $this->changesComparator = $changesComparator;
    }

    public function hasChangesInFiles(UuidInterface $flowUuid, CodeReference $codeReference, array $fileGlobs) : bool
    {
        $flow = $this->flatFlowRepository->find($flowUuid);
        $lastSuccessfulTides = $this->tideRepository->findLastSuccessfulByFlowUuidAndBranch($flowUuid, $codeReference->getBranch(), 1);

        if (0 === count($lastSuccessfulTides)) {
            $defaultBranch = $flow->getRepository()->getDefaultBranch();

            if ($codeReference->getBranch() == $defaultBranch) {
                return true;
            }

            $base = $defaultBranch;
        } else {
            $lastSuccessfulTide = current($lastSuccessfulTides);
            $base = $lastSuccessfulTide->getCodeReference()->getCommitSha();
        }

        $changedFiles = $this->changesComparator->listChangedFiles($flow, $base, $codeReference->getCommitSha());
        foreach ($fileGlobs as $glob) {
            $regex = $this->globToRegex($glob);

            foreach ($changedFiles as $changedFile) {
                if (preg_match($regex, $changedFile)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function globToRegex(string $glob) : string
    {
        return str_replace(
            [
                '\\*\\*',
                '\\*'
            ], [
                '.*',
                '[^\/]*',
            ],
            '#^'.preg_quote($glob, '#').'$#'
        );
    }
}
