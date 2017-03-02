<?php

namespace ContinuousPipe\Builder\Reporting;

use ContinuousPipe\Builder\Aggregate\BuildRepository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Events\AggregateNotFound;

class BuildReportFromAggregate implements ReportBuilder
{
    /**
     * @var BuildRepository
     */
    private $buildRepository;

    /**
     * @param BuildRepository $buildRepository
     */
    public function __construct(BuildRepository $buildRepository)
    {
        $this->buildRepository = $buildRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $buildIdentifier): array
    {
        try {
            $build = $this->buildRepository->find($buildIdentifier);
        } catch (AggregateNotFound $e) {
            throw new ReportException('Can\'t create a report for an unknown build', $e->getCode(), $e);
        }

        return [
            'status' => $build->getStatus(),
            'image' => $this->getImageFromRequest($build->getRequest()),
            'steps' => [
                'count' => count($build->getRequest()->getSteps()),
            ],
            'artifacts' => [
                'read' => [
                    'count' => count($this->getReadArtifacts($build->getRequest()))
                ],
                'write' => [
                    'count' => count($this->getWriteArtifacts($build->getRequest()))
                ]
            ]
        ];
    }

    /**
     * @param BuildRequest $request
     *
     * @return array
     */
    private function getImageFromRequest(BuildRequest $request)
    {
        $imageName = '[unknown]';
        $tagName = '[unknown]';

        foreach ($request->getSteps() as $step) {
            if (null !== ($image = $step->getImage())) {
                $imageName = $image->getName();
                $tagName = $image->getTag();
            }
        }

        return [
            'name' => $imageName,
            'tag' => $tagName,
        ];
    }

    private function getReadArtifacts(BuildRequest $request)
    {
        $artifacts = [];

        foreach ($request->getSteps() as $step) {
            $artifacts = array_merge($artifacts, $step->getReadArtifacts());
        }

        return $artifacts;
    }

    private function getWriteArtifacts(BuildRequest $request)
    {
        $artifacts = [];

        foreach ($request->getSteps() as $step) {
            $artifacts = array_merge($artifacts, $step->getWriteArtifacts());
        }

        return $artifacts;
    }
}
