<?php

namespace ContinuousPipe\River\Flow\Configuration;

use Symfony\Component\Config\Definition\PrototypedArrayNode;

class KeyIndexedArrayNode extends PrototypedArrayNode
{
    /**
     * {@inheritdoc}
     */
    protected function mergeValues($leftSide, $rightSide)
    {
        if (false === $rightSide) {
            // if this is still false after the last config has been merged the
            // finalization pass will take care of removing this key entirely
            return false;
        }

        if (false === $leftSide || !$this->performDeepMerging) {
            return $rightSide;
        }

        foreach ($rightSide as $k => $v) {
            if (!array_key_exists($k, $leftSide)) {
                $leftSide[$k] = $v;
                continue;
            }

            $this->prototype->setName($k);
            $leftSide[$k] = $this->prototype->merge($leftSide[$k], $v);
        }

        return $leftSide;
    }
}
