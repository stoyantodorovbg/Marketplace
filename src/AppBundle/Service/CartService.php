<?php


namespace AppBundle\Service;


use AppBundle\Entity\Cart;
use AppBundle\Entity\Currency;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPurchase;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class CartService implements CartServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addProduct(Product $product, User $user, int $addQuantity, Currency $currency, float $priceOrder)
    {
        $em = $this->entityManager;

        $activePromotions = $this->findActivePromotions($product, $user);
        $bestPromotion = false;
        $reducedPrice = false;
        if (count($activePromotions) > 0) {
            $bestPromotion = $this->getBestPromotion($activePromotions);
            $reducedPrice = $this->calculateReduction($product, $bestPromotion->getpercentsDiscount());
            $priceOrder = $reducedPrice * $addQuantity;
        }

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setProduct($product);
        $cart->setPrice($priceOrder);
        $cart->setCurrency($currency);
        $cart->setBought(0); // is not bought
        $cart->setRefused(0); // is not refused

        if ($bestPromotion) {
            $cart->setIsInPromotion(1);
        } else {
            $cart->setIsInPromotion(0);
        }

        $cart->setQuantity($addQuantity);
        $em->persist($cart);
        $em->flush();
    }

    public function buyAction($user)
    {
        $userId = $user->getId();
        $userCurrency = $user->getUserProfile()->getCurrency();
        $cartRepo = $this->entityManager->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $userId]);
        $em = $this->entityManager;

        foreach($addsInCart as $add) {
            if ($add->isBought() != 1 && $add->isRefused() != 1) {
                $add->setBought(1);
                $em->persist($add);
                $em->flush();

                $product = $add->getProduct();
                $addQuantity = $add->getQuantity();;
                $purchaseValue = $this->calculateAddInUserCurrency($add, $user);
                $productRepo = $this->entityManager->getRepository(Product::class);
                $addTotal = $product->getPrice() * $addQuantity;
                $productId = $product->getId();
                $em->persist($product);
                $em->flush();

                $userProfileSeller = $productRepo->find($productId)->getUser()->getUserProfile();
                $userProfileSeller->setCash($userProfileSeller->getCash() + $addTotal);
                $userProfileSeller->setSalesCount($userProfileSeller->getSalesCount() + 1);
                $userProfileSeller->setRating($userProfileSeller->getRating() + 0.1);
                $userProfileSeller->setSalesValue($userProfileSeller->getSalesValue() + $purchaseValue);
                if ($userProfileSeller->getIsSeller() == 0) {
                    $userProfileSeller->setIsSeller(1);
                }
                $em->persist($userProfileSeller);
                $em->flush();

                $userProfileBuyer = $user->getUserProfile();
                $userProfileBuyer->setPurchaseCount($userProfileBuyer->getPurchaseCount() + 1);
                $userProfileBuyer->setRating($userProfileBuyer->getRating() + 0.2);
                $userProfileBuyer->setPurchasesValue($userProfileBuyer->getPurchasesValue() + $purchaseValue);
                $em->persist($userProfileBuyer);
                $em->flush();

                $userPurchase = new UserPurchase();
                $userPurchase->setUser($user);
                $userPurchase->setProduct($product);
                $userPurchase->setQuantity($addQuantity);
                $userPurchase->setValue($purchaseValue);
                $userPurchase->setDateCreated(new \DateTime());
                $em->persist($userPurchase);
                $em->flush();

                $userPurchaseRepo = $this->entityManager->getRepository(UserPurchase::class);
            }
        }
    }

    public function refuse(Request $request, Cart $cart)
    {
        $product = $cart->getProduct();
        $addQuantity = $cart->getQuantity();
        $product->setQuantity($product->getQuantity() + $addQuantity);

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $em = $this->entityManager;
        $em->persist($product);
        $em->flush();

        $cartRepo = $this->entityManager->getRepository(Cart::class);
        $cartRepo->refuseProduct($cart->getId());

        $connection->commit();
    }

    public function return(Request $request, Cart $cart)
    {
        $product = $cart->getProduct();
        $addQuantity = $cart->getQuantity();
        $product->setQuantity($product->getQuantity() - $addQuantity);

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        $em = $this->entityManager;
        $em->persist($product);
        $em->flush();

        $cartRepo = $this->entityManager->getRepository(Cart::class);
        $cartRepo->returnProduct($cart->getId());

        $connection->commit();
    }

    public function calculateCartBill(User $user)
    {
        $this->entityManager->getRepository(Cart::class);
        $userId = $user->getId();
        $userCurrency = $user->getUserProfile()->getCurrency();
        $cartRepo = $this->entityManager->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $userId]);
        $cartBill = 0;

        foreach($addsInCart as $add) {
            if ($add->isBought() != 1 && $add->isRefused() != 1) {
                $priceAddInUserCurrency = $this->calculateAddInUserCurrency($add, $user);
                $cartBill += $priceAddInUserCurrency;
            }
        }

        return $cartBill;
    }

    public function calculateAddInUserCurrency(Cart $add, User $user)
    {
        $userCurrency = $user->getUserProfile()->getCurrency();
        $priceAddInEuro = 0;
        if ($add->getCurrency()->getExchangeRateEUR() != 1) {
            $priceAddInEuro = $add->getPrice() * $add->getCurrency()->getExchangeRateEUR();
        } else {
            $priceAddInEuro = $add->getPrice();
        }

        $priceAddInUserCurrency = 0;
        if ($userCurrency->getExchangeRateEUR() != 1) {
            $priceAddInUserCurrency = $priceAddInEuro / $userCurrency->getExchangeRateEUR();
        } else {
            $priceAddInUserCurrency = $priceAddInEuro;
        }

        return number_format($priceAddInUserCurrency, 2);
    }

    public function findActivePromotions(Product $product, User $user)
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