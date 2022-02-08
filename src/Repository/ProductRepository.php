<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
    //  */

    public function findByPrice($price, $gender)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.price <= :val')
            ->setParameter('val', $price)
            ->andWhere('p.gender = :gender')
            ->setParameter('gender', $gender)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }




    public function findByPriceSubCategory($price, $gender, $subCat)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.price <= :val')
            ->setParameter('val', $price)
            ->andWhere('p.gender = :gender')
            ->setParameter('gender', $gender)
            ->andWhere('p.category = :subCat')
            ->setParameter('subCat', $subCat)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCategoryPrice($gender,$value, $price)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->innerJoin('c.subCategory', 's')
            ->andWhere('c.subCategory = :value')
            ->setParameter('value', $value)
            ->andWhere('p.gender = :gender')
            ->andWhere('p.price <= :price')
            ->setParameter('price', $price)
            ->setParameter('gender', $gender)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();

    }

    public function findByCategory($gender,$value)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->innerJoin('c.subCategory', 's')
            ->andWhere('c.subCategory = :value')
            ->setParameter('value', $value)
            ->andWhere('p.gender = :gender')
            ->setParameter('gender', $gender)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();

    }




}
