<?php

namespace ContinuousPipe\River\Tests\CodeRepository;

use ContinuousPipe\River\CodeRepository\Organisation;
use ContinuousPipe\River\CodeRepository\OrganisationRepository;

class InMemoryOrganisationRepository implements OrganisationRepository
{
    private $organisations = [];

    /**
     * {@inheritdoc}
     */
    public function findByCurrentUser()
    {
        return $this->organisations;
    }

    /**
     * Add a new organisation
     *
     * @param Organisation $organisation
     */
    public function add(Organisation $organisation)
    {
        $this->organisations[$organisation->getIdentifier()] = $organisation;
    }
}
