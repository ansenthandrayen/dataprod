<?php

namespace App\Repository;

use App\Entity\ReferenceProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReferenceProduit>
 */
class ReferenceProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferenceProduit::class);
    }

        /**
     * Recherche par référence ou description. Sans terme, renvoie tous les produits.
     *
     * @return ReferenceProduit[]
     */
    public function rechercher(?string $terme): array
    {
        $qb = $this->createQueryBuilder('p')->orderBy('p.reference', 'ASC');

        $terme = trim((string) $terme);
        if ('' !== $terme) {
            // % et _ sont des jokers de LIKE : on les neutralise pour une recherche littérale
            $motif = '%' . addcslashes($terme, '%_\\') . '%';
            $qb->andWhere('p.reference LIKE :motif OR p.description LIKE :motif')
                ->setParameter('motif', $motif);
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return ReferenceProduit[] Returns an array of ReferenceProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReferenceProduit
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
