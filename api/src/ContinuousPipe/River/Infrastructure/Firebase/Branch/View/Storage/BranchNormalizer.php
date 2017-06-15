<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Branch\View\Storage;

use ContinuousPipe\River\CodeRepository\Branch;
use ContinuousPipe\River\View\Tide;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class BranchNormalizer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function normalizeBranches(array $branches)
    {
        return array_combine(
            array_map(
                function (Branch $branch) {
                    return hash('sha256', (string) $branch);
                },
                $branches
            ),
            array_map([$this, 'normalizeBranch'], $branches)
        );
    }

    public function normalizeBranch(Branch $branch)
    {
        return [
            'latest-tides' => $this->normalizeTides($branch->getTides()),
            'pinned' => $branch->isPinned(),
            'name' => (string) $branch
        ];
    }

    public function normalizeTides(array $tides): array
    {
        return array_combine(
            array_map(
                function (Tide $tide) {
                    return $tide->getUuid();
                },
                $tides
            ),
            array_map([$this, 'normalizeTide'], $tides)
        );
    }

    public function normalizeTide(Tide $tide): array
    {
        $context = SerializationContext::create();
        $context->setGroups(['Default']);

        return \GuzzleHttp\json_decode($this->serializer->serialize($tide, 'json', $context), true);
    }

}