<?php

namespace App\Repository;

use App\Entity\Handset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class HandsetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Handset::class);
    }

    /**
     * @param array $filters
     * @return array{0: Handset[], 1: int, 2: int}
     */
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('h')
            ->join('h.brand', 'b');

        if ($filters['brand']) {
            $qb->andWhere('b.name = :brand')->setParameter('brand', $filters['brand']);
        }
        if ($filters['price_min'] !== null) {
            $qb->andWhere('h.price >= :min')->setParameter('min', $filters['price_min']);
        }
        if ($filters['price_max'] !== null) {
            $qb->andWhere('h.price <= :max')->setParameter('max', $filters['price_max']);
        }
        if ($filters['release_year'] !== null) {
            $qb->andWhere('YEAR(h.releaseDate) = :year')->setParameter('year', $filters['release_year']);
        }

        foreach ($filters['features'] as $idx => $feature) {
            $qb->andWhere("h.features LIKE :feature$idx")
                ->setParameter("feature$idx", '%' . $feature . '%');
        }



        if ($filters['search']) {
            $qb->andWhere('h.name LIKE :search OR h.description LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Always order by name for deterministic results
        $qb->orderBy('h.name', 'ASC');

        // Pagination
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 20;
        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $query = $qb->getQuery();
        $paginator = new Paginator($query, fetchJoinCollection: true);

        $total = count($paginator);
        $lastPage = (int) ceil($total / $perPage);

        return [iterator_to_array($paginator), $total, $lastPage];
    }
}
