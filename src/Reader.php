<?php

namespace KarimTao\ServerStatus;

abstract class Reader
{
    abstract public function cpu(): int;

    abstract public function load(): array;

    abstract public function cores(): int;

    abstract public function memory(): array;

    abstract public function uptime(): int;

    abstract public function process(): string;

    abstract public function processes(): int;

    abstract protected function read(string $source, string $metric): string;
}
