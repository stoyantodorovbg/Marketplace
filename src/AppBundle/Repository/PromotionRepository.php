<?php

namespace AppBundle\Repository;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
/**
 * PromotionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PromotionRepository extends \Doctrine\ORM\EntityRepository
{
    public function findUserByRating($rating)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('u')
            ->from(UserProfile::class, 'u')
            ->where('u.rating >= ?1')
            ->setParameter(1, $rating)
            ->getQuery();
        $userProfiles = $query->execute();
        return $userProfiles;
    }

    public function findUserByPurchaseValue($purchaseValue)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('u')
            ->from(UserProfile::class, 'u')
            ->join('u.currency', 'c')
            ->where('u.purchasesValue * c.exchangeRateEUR >= ?1')
            ->setParameter(1, $purchaseValue)
            ->getQuery();
        $userProfiles = $query->execute();
        return $userProfiles;
    }

    public function findUserByPurchaseCount($purchaseCount)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('u')
            ->from(UserProfile::class, 'u')
            ->where('u.purchaseCount >= ?1')
            ->setParameter(1, $purchaseCount)
            ->getQuery();
        $userProfiles = $query->execute();
        return $userProfiles;
    }

    public function findUserByCash($cash)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('u')
            ->from(UserProfile::class, 'u')
            ->join('u.currency', 'c')
            ->where('u.cash * c.exchangeRateEUR >= ?1')
            ->setParameter(1, $cash)
            ->getQuery();
        $userProfiles = $query->execute();
        return $userProfiles;
    }

    public function findUserByDateCreated($dateCreated)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.dateCreated <= ?1')
            ->setParameter(1, $dateCreated)
            ->getQuery();
        $users = $query->execute();
        return $users;
    }

    public function getActivePromotionByDate()
    {
        $now = new \DateTime();
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('p')
            ->from(Promotion::class, 'p')
            ->where('p.startDate <= ?1 AND p.endDate >= ?1')
            ->setParameter(1, $now)
            ->getQuery();
        $promotions = $query->execute();
        return $promotions;

    }

    public function getProductsIds($promotionId)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('pp.id')
            ->from(Promotion::class, 'p')
            ->join('p.products', 'pp')
            ->where('p.id = ?1')
            ->setParameter(1, $promotionId)
            ->getQuery();
        $productsIdsArr = $query->execute();
        $productsIds = $this->convertToOneDimensionalArray($productsIdsArr);
        return $productsIds;
    }

    public function getCategoriesIds($promotionId)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('c.id')
            ->from(Promotion::class, 'p')
            ->join('p.categories', 'c')
            ->where('p.id = ?1')
            ->setParameter(1, $promotionId)
            ->getQuery();
        $categoriesIdsArr = $query->execute();
        $categoriesIds = $this->convertToOneDimensionalArray($categoriesIdsArr);
        return $categoriesIds;
    }

    public function getUsersIds($promotionId)
    {
        $em = $this->getEntityManager();
        $db = $em->createQueryBuilder();
        $query = $db
            ->select('u.id')
            ->from(Promotion::class, 'p')
            ->join('p.users', 'u')
            ->where('p.id = ?1')
            ->setParameter(1, $promotionId)
            ->getQuery();
        $usersIdsArr = $query->execute();
        $usersIds = $this->convertToOneDimensionalArray($usersIdsArr);
        return $usersIds;
    }

    private function convertToOneDimensionalArray($array)
    {
        $resultArray = [];
        foreach ($array as $arr) {
            $resultArray[] = $arr['id'];
        }
        return $resultArray;
    }


}
