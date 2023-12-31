<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public const ENTITY_ALIAS = 'p';

    private ?QueryBuilder $qb = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        $flush && $this->getEntityManager()->flush();
    }

    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        $flush && $this->getEntityManager()->flush();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->qb ?: $this->createQueryBuilder(self::ENTITY_ALIAS);
    }

    public function findAllNotArchived(): array
    {
        return ($qb = $this->getQueryBuilder())
            ->where($qb->expr()->isNull('p.archivedAt'))
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findNotArchivedByName(string $name): ?Project
    {
        return ($qb = $this->getQueryBuilder())
            ->where($qb->expr()->isNull('p.archivedAt'))
            ->andWhere($qb->expr()->eq('p.name', ':name'))
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
    //    /**
    //     * @return Project[] Returns an array of Project objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Project
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
