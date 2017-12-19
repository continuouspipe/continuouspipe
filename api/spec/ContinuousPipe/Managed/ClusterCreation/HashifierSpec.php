<?php

namespace spec\ContinuousPipe\Managed\ClusterCreation;

use ContinuousPipe\Managed\ClusterCreation\Hashifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HashifierSpec extends ObjectBehavior
{
    function its_max_length_uses_whole_string_if_not_too_long()
    {
        $this::maxLength('ft-project', 10)->shouldBeLike('ft-project');
    }

    function its_max_length_uses_a_hash_at_the_end_if_too_long()
    {
        $this::maxLength('continuous-pipe-project', 10)->shouldBeLike('conti4bc36');
    }
}
