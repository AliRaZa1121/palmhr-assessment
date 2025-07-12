<?php

namespace App\Controller\Api\V1;

use App\Repository\HandsetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HandsetController extends AbstractController
{
    #[Route('/handsets', name: 'api_v1_handsets', methods: ['GET'])]
    public function index(Request $request, HandsetRepository $handsetRepository): JsonResponse
    {
        $filters = $this->parseFilters($request);

        // Prevent excessively large per_page
        $filters['per_page'] = min($filters['per_page'], 100);

        // Get paginated/filter results
        [$handsets, $total, $lastPage] = $handsetRepository->findByFilters($filters);

        return $this->json([
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
        ]);
    }

    private function parseFilters(Request $request): array
    {
        // Validate and sanitize query parameters here for robustness
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
