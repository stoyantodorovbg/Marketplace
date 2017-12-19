<?php


namespace AppBundle\Service;


use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPurchase;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;

class ProductService implements ProductServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function removeFromSale(Product $product)
    {
        $productOwner = $product->getUser();
        $quantity = $product->getQuantity();
        $purchaseValue = $product->getPrice();

        $userPurchase = new UserPurchase();
        $userPurchase->setUser($productOwner);
        $userPurchase->setProduct($product);
        $userPurchase->setQuantity($quantity);
        $userPurchase->setValue($purchaseValue);
        $userPurchase->setDateCreated(new \DateTime());

        $em = $this->entityManager;

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $em->persist($userPurchase);
        $em->flush();

        $em->remove($product);
        $em->flush();

        $connection->commit();
    }

    public function delete(Product $product, Form $form)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->remove($product);
            $em->flush();
        }
    }

    public function findActivePromotions(Product $product, User $user):array
    {
        $promotionRepo = $this->entityManager->getRepository(Promotion::class);
        $productRepo = $this->entityManager->getRepository(Product::class);
        $productsCategoriesIds = $productRepo->getCategoriesIds($product->getId());

        $promotionsByDate = $promotionRepo->getActivePromotionByDate();
        $activePromotions = [];

        foreach($promotionsByDate as $promotion) {
            $promotionId = $promotion->getId();
            $promotionType = $promotion->getType();
            switch ($promotionType) {
                case 'certain_products':
                    $promoProductsIds = $promotionRepo->getProductsIds($promotionId);
                    if (in_array($product->getId(), $promoProductsIds)) {
                        $activePromotions[] = $promotion;
                    }
                    break;
                case 'all_products':
                    $activePromotions[] = $promotion;
                    break;
                case 'certain_categories':
                    $promoCategoriesIds = $promotionRepo->getCategoriesIds($promotionId);
                    $isInCategory = false;
                    foreach($productsCategoriesIds as $id) {
                        if (in_array($id, $promoCategoriesIds)) {
                            $isInCategory = true;
                        }
                    }
                    if ($isInCategory) {
                        $activePromotions[] = $promotion;
                    }
                    break;
                case 'certain_users':
                    $promoUsersIds = $promotionRepo->getUsersIds($promotionId);
                    if (in_array($user->getid(), $promoUsersIds)) {
                        $activePromotions[] = $promotion;
                    }
                    break;
            }
        }

        return $activePromotions;
    }

    public function getBestPromotion(array $promotions)
    {
        $bestPromotion = $promotions[0];
        foreach ($promotions as $promotion) {
            if ($promotion->getPercentsDiscount() > $bestPromotion->getPercentsDiscount()) {
                $bestPromotion = $promotion;
            }
        }
        return $bestPromotion;
    }

    public function calculateReduction(Product $product, int $percentsDiscount)
    {
        $productPrice = $product->getPrice();
        $reducedPrice = $productPrice - (($productPrice * $percentsDiscount) / 100);
        return number_format($reducedPrice, 2);
    }

}