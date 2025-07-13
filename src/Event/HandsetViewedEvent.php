<?php    
namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class HandsetViewedEvent extends Event
{
    public function __construct(
        public readonly int $handsetId,
        public readonly string $timestamp,
        public readonly string $apiVersion,
    ) {}
}
