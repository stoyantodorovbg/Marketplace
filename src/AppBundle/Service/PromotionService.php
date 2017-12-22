<?php


namespace AppBundle\Service;

use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Form;

class PromotionService implements PromotionServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function newForCertainProducts():Promotion
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $allUsers = $userRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setType('certain_products');


        return $promotion;
    }

    public function newForAllProducts():Promotion
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $allUsers = $userRepo->findAll();

        $productRepo = $this->entityManager->getRepository(Product::class);
        $allProducts = $productRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setProducts($allProducts);
        $promotion->setType('all_products');

        return $promotion;
    }

    public function newForAllUsers():Promotion
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $allUsers = $userRepo->findAll();
        $productRepo = $this->entityManager->getRepository(Product::class);
        $allProducts = $productRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setProducts($allProducts);
        $promotion->setType('all_products');

        return $promotion;
    }

    public function newForCertainCategories():Promotion
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $allUsers = $userRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setType('certain_categories');

        return $promotion;
    }

    public function findUsersByUserProfiles($userProfiles):array
    {
        $users = [];
        foreach ($userProfiles as $userProfile) {
            $userProfileId = $userProfile->getId();
            $userRepo = $this->entityManager->getRepository(User::class);
            $user = $userRepo->findOneBy(['userProfile' => $userProfile]);
            $users[] = $user;
        }

        return $users;
    }

    public function newForCertainUsers($users):Promotion
    {
        $productsRepo = $this->entityManager->getRepository(Product::class);
        $products = $productsRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($users);
        $promotion->setProducts($products);
        $promotion->setType('certain_users');

        return $promotion;
    }
}