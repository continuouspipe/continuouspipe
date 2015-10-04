<?php

namespace ContinuousPipe\River\Filter\View;

use ContinuousPipe\River\CodeReference;

class CodeReferenceView
{
    /**
     * @var string
     */
    public $branch;

    /**
     * @var string
     */
    public $sha1;

    /**
     * @param CodeReference $codeReference
     *
     * @return CodeReferenceView
     */
    public static function fromCodeReference(CodeReference $codeReference)
    {
        $self = new self();
        $self->branch = $codeReference->getBranch();
        $self->sha1 = $codeReference->getCommitSha();

        return $self;
    }
}
