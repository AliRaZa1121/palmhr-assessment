<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PriceFilterAppliedEvent extends Event
{
    public function __construct(
        public readonly ?float $min,
        public readonly ?float $max,
        public readonly int $resultCount,
        public readonly string $timestamp,
        public readonly string $apiVersion,
    ) {}
}
