<?php

namespace KarimTao\ServerStatus;

use RuntimeException;

final class MetricUnavailableException extends RuntimeException
{
    public static function for(string $metric, string $reason): self
    {
        return new self('Unable to read ' . $metric . ': ' . $reason);
    }
}
