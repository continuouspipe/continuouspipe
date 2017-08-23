<?php


namespace ContinuousPipe\River\Managed\Resources\Calculation;

class ResourceConverter
{
    private static $cpuDivisor = 1000;

    public static function resourceToNumber(string $value) : float
    {
        if (substr($value, -2) == 'Gi') {
            return floatval(substr($value, 0, -2)) * 1000;
        } elseif (substr($value, -2) == 'Mi') {
            return floatval(substr($value, 0, -2));
        }

        if (substr($value, -1) == 'm') {
            return floatval(substr($value, 0, -1)) / self::$cpuDivisor;
        }

        return floatval($value);
    }

    public static function cpuToString(float $value) : string
    {
        return ($value * self::$cpuDivisor) . 'm';
    }

    public static function memoryToString(float $value) : string
    {
        return $value . 'Mi';
    }
}
