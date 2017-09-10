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

        if (null !== ($lastTide = $this->getLastSuccessfulTideForBranch($flowUuid, $codeReference->getBranch()))) {
            $base = $lastTide->getCodeReference()->getCommitSha();
        } else {
            $base = $flow->getRepository()->getDefaultBranch();
        }

        return $this->hasFilesWatchedByGlobs(
            $this->changesComparator->listChangedFiles($flow, $base, $codeReference->getCommitSha()),
            $fileGlobs
        );
    }

    private function hasFilesWatchedByGlobs(array $files, array $globs)
    {
        foreach ($globs as $glob) {
            foreach ($files as $changedFile) {
                if ($this->fileIsMatchingGlob($glob, $changedFile)) {
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
            ],
            [
                '.*',
                '[^\/]*',
            ],
            '#^'.preg_quote($glob, '#').'$#'
        );
    }

    private function getLastSuccessfulTideForBranch(UuidInterface $flowUuid, string $branch)
    {
        $lastSuccessfulTides = $this->tideRepository->findLastSuccessfulByFlowUuidAndBranch($flowUuid, $branch, 1);

        if (count($lastSuccessfulTides) == 0) {
            return null;
        }

        return $lastSuccessfulTides[0];
    }

    private function fileIsMatchingGlob(string $glob, string $changedFile) : bool
    {
        return preg_match($this->globToRegex($glob), $changedFile);
    }
}
