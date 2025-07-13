<?php

namespace App\Controller\Api\V2;

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

    #[Route('/handsets', name: 'api_v2_handsets', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'brand' => $request->query->get('brand'),
            'price_min' => $request->query->get('price_min'),
            'price_max' => $request->query->get('price_max'),
            'release_year' => $request->query->get('release_year'),
            'features' => $request->query->all('features'),
            'search' => $request->query->get('search'),
            'page' => $request->query->getInt('page', 1),
            'per_page' => $request->query->getInt('per_page', 20),
        ];

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
                    apiVersion: 'v2'
                );
                $this->dispatcher->dispatch($event);
            }

            // Dispatch HandsetViewedEvent for each handset in the result
            foreach ($handsets as $handset) {
                $event = new HandsetViewedEvent(
                    handsetId: $handset->getId(),
                    timestamp: (new \DateTime())->format(DATE_ATOM),
                    apiVersion: 'v2'
                );
                $this->dispatcher->dispatch($event);
            }

            // Build pagination links
            $pagination = [
                'total_items'    => $total,
                'items_per_page' => $filters['per_page'],
                'current_page'   => $filters['page'],
                'total_pages'    => $lastPage,
                'links' => [
                    'first' => '/api/v2/handsets?page=1',
                    'last'  => "/api/v2/handsets?page={$lastPage}",
                    'next'  => $filters['page'] < $lastPage ? "/api/v2/handsets?page=" . ($filters['page'] + 1) : null,
                    'prev'  => $filters['page'] > 1 ? "/api/v2/handsets?page=" . ($filters['page'] - 1) : null,
                ]
            ];

            return [
                'data' => array_map([$this, 'transformHandsetV2'], $handsets),
                'pagination' => $pagination,
            ];
        });

        return $this->json($response);
    }

    private function getCacheKey(array $filters): string
    {
        return 'api_v2_handsets_' . md5(json_encode($filters));
    }

    private function transformHandsetV2($handset): array
    {
        // You may need to adjust this logic depending on your Handset entity implementation.
        $discount = $handset->getDiscountPercentage();
        $amount = $handset->getPrice();
        $finalPrice = round($amount * (1 - $discount / 100), 2);

        return [
            'id' => $handset->getId(),
            'name' => $handset->getName(),
            'brand' => [
                'id' => $handset->getBrand()->getId(),
                'name' => $handset->getBrand()->getName(),
                'country' => $handset->getBrand()->getCountry(),
            ],
            'price' => [
                'amount' => $amount,
                'currency' => $handset->getCurrency(),
                'discount_percentage' => $discount,
                'final_price' => $finalPrice,
            ],
            'release_date' => $handset->getReleaseDate()?->format('Y-m-d'),
            'features' => $handset->getFeatures(),
            'specifications' => $handset->getSpecifications(),
            'description' => $handset->getDescription(),
        ];
    }
}
