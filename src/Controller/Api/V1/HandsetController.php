<?php

namespace App\Controller\Api\V1;

use App\Repository\HandsetRepository;
use App\Service\CacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Event\HandsetViewedEvent;
use App\Event\PriceFilterAppliedEvent;

class HandsetController extends AbstractController
{
    public function __construct(
        private readonly CacheService $cacheService,
        private readonly HandsetRepository $handsetRepository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    #[Route('/handsets', name: 'api_v1_handsets', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $filters = $this->parseFilters($request);
        $filters['per_page'] = min($filters['per_page'], 100);

        $cacheKey = $this->getCacheKey($filters);

        $response = $this->cacheService->getOrSet($cacheKey, function () use ($filters) {
            [$handsets, $total, $lastPage] = $this->handsetRepository->findByFilters($filters);

            // Dispatch PriceFilterAppliedEvent if price filters are used
            if ($filters['price_min'] !== null || $filters['price_max'] !== null) {
                $event = new PriceFilterAppliedEvent(
                    min: $filters['price_min'],
                    max: $filters['price_max'],
                    resultCount: $total,
                    timestamp: (new \DateTime())->format(DATE_ATOM),
                    apiVersion: 'v1'
                );
                $this->dispatcher->dispatch($event);
            }

            // Dispatch HandsetViewedEvent for each handset in the result
            foreach ($handsets as $handset) {
                $event = new HandsetViewedEvent(
                    handsetId: $handset->getId(),
                    timestamp: (new \DateTime())->format(DATE_ATOM),
                    apiVersion: 'v1'
                );
                $this->dispatcher->dispatch($event);
            }

            return [
                'data' => array_map([$this, 'transformHandsetV1'], $handsets),
                'meta' => [
                    'total' => $total,
                    'per_page' => $filters['per_page'],
                    'current_page' => $filters['page'],
                    'last_page' => $lastPage,
                    'filters_applied' => array_filter([
                        'brand' => $filters['brand'],
                        'features' => $filters['features'] ?: null,
                    ]),
                ],
            ];
        });

        return $this->json($response);
    }

    /**
     * Build cache key from filters.
     */
    private function getCacheKey(array $filters): string
    {
        return 'api_v1_handsets_' . md5(json_encode($filters));
    }

    /**
     * Sanitize/parse API filters.
     */
    private function parseFilters(Request $request): array
    {
        return [
            'brand' => $request->query->get('brand'),
            'price_min' => $request->query->has('price_min') ? floatval($request->query->get('price_min')) : null,
            'price_max' => $request->query->has('price_max') ? floatval($request->query->get('price_max')) : null,
            'release_year' => $request->query->has('release_year') ? intval($request->query->get('release_year')) : null,
            'features' => $request->query->all('features'),
            'search' => $request->query->get('search'),
            'page' => max(1, $request->query->getInt('page', 1)),
            'per_page' => max(1, $request->query->getInt('per_page', 20)),
        ];
    }

    /**
     * Transform a Handset entity for v1 output.
     */
    private function transformHandsetV1($handset): array
    {
        return [
            'id' => $handset->getId(),
            'name' => $handset->getName(),
            'brand' => $handset->getBrand()->getName(),
            'price' => $handset->getPrice(),
            'release_date' => $handset->getReleaseDate()?->format('Y-m-d'),
            'features' => $handset->getFeatures(),
            'specifications' => $handset->getSpecifications(),
            'description' => $handset->getDescription(),
        ];
    }
}
