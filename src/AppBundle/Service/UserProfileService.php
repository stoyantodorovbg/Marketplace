<?php


namespace AppBundle\Service;


use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPurchase;
use Doctrine\ORM\EntityManager;

class UserProfileService implements UserProfileServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function putPurchaseOnSale(Product $product, User $user, UserPurchase $userPurchase)
    {
        $userCurrency = $user->getUserProfile()->getCurrency();
        $product->setUser($user);
        $product->setCurrency($userCurrency);
        $product->setQuantity($userPurchase->getQuantity());
        $product->setImage($userPurchase->getProduct()->getImage());
        $product->setUser($user);
        $product->setCurrency($userCurrency);
        $product->setAvailability(1);
        $em = $this->entityManager;

        $userProfile = $user->getUserProfile();
        $userProfile->setIsSeller(1);

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $em->persist($product);
        $em->flush();

        $em->persist($userProfile);
        $em->flush();

        $em->remove($userPurchase);
        $em->flush();

        $connection->commit();
    }
}