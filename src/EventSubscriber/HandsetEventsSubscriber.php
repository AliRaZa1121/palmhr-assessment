<?php

namespace App\EventSubscriber;

use App\Event\HandsetViewedEvent;
use App\Event\PriceFilterAppliedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HandsetEventsSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public static function getSubscribedEvents(): array
    {
        return [
            HandsetViewedEvent::class => 'onHandsetViewed',
            PriceFilterAppliedEvent::class => 'onPriceFilterApplied',
        ];
    }

    public function onHandsetViewed(HandsetViewedEvent $event): void
    {
        $this->logger->info('Handset viewed', [
            'handsetId'  => $event->handsetId,
            'timestamp'  => $event->timestamp,
            'apiVersion' => $event->apiVersion,
        ]);
    }

    public function onPriceFilterApplied(PriceFilterAppliedEvent $event): void
    {
        $this->logger->info('Price filter applied', [
            'min'        => $event->min,
            'max'        => $event->max,
            'resultCount' => $event->resultCount,
            'timestamp'  => $event->timestamp,
            'apiVersion' => $event->apiVersion,
        ]);
    }
}
