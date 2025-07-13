<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ApiRateLimitSubscriber implements EventSubscriberInterface
{
    private RateLimiterFactory $apiLimiter;

    public function __construct(RateLimiterFactory $apiLimiter)
    {
        $this->apiLimiter = $apiLimiter;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (strpos($request->getPathInfo(), '/api/') !== 0) {
            return;
        }

        $limiter = $this->apiLimiter->create($request->getClientIp());
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(
                $limit->getRetryAfter()->getTimestamp() - time(),
                'Too many requests, please slow down.'
            );
        }
    }

    public static function getSubscribedEvents() : array
    {
        return [
            'kernel.request' => ['onKernelRequest', 15],
        ];
    }
}
