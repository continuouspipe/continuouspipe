<?php


namespace ContinuousPipe\River\ClusterPolicies\Resources;

class ResourceConverter
{
    public static function resourceToNumber(string $value) : float
    {
        if (substr($value, -2) == 'Gi') {
            return intval(substr($value, 0, -2)) * 1000;
        } elseif (substr($value, -2) == 'Mi') {
            return intval(substr($value, 0, -2));
        }

        if (substr($value, -1) == 'm') {
            return intval(substr($value, 0, -1)) / 1000;
        }

        return intval($value);
    }
}
