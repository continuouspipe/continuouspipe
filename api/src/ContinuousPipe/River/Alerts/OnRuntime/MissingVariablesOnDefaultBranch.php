<?php

namespace ContinuousPipe\River\Alerts\OnRuntime;

use ContinuousPipe\River\Alerts\Alert;
use ContinuousPipe\River\Alerts\AlertAction;
use ContinuousPipe\River\Alerts\AlertsRepository;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\MissingVariables\MissingVariableResolver;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class MissingVariablesOnDefaultBranch implements AlertsRepository
{
    /**
     * @var MissingVariableResolver
     */
    private $missingVariableResolver;

    /**
     * @param MissingVariableResolver $missingVariableResolver
     */
    public function __construct(MissingVariableResolver $missingVariableResolver)
    {
        $this->missingVariableResolver = $missingVariableResolver;
    }

    /**
     * @param FlatFlow $flow
     *
     * @return Alert[]
     */
    public function findByFlow(FlatFlow $flow)
    {
        $missingVariables = $this->missingVariableResolver->findMissingVariables(
            $flow,
            CodeReference::repositoryDefault($flow->getRepository())
        );

        if (count($missingVariables) == 0) {
            return [];
        }

        return [
            new Alert(
                'missing-variable',
                sprintf('%d variable(s) are missing in the configuration', count($missingVariables)),
                new \DateTime(),
                new AlertAction(
                    'state',
                    'Configure',
                    'flow.configuration'
                )
            ),
        ];
    }
}
