<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Entity\UserPurchase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Cart controller.
 *
 * @Route("cart")
 */
class CartController extends Controller
{
    /**
     * Creates a new cart entity.
     *
     * @Route("/new", name="cart_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function newAction(Request $request)
    {
        $cart = $this->getUser()->getCart();
        if ($cart !== null) {
            return $this->redirectToRoute('cart_edit', ['id' => $cart->getId()]);
        }
        $cart = new Cart();

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($this->getUser()->getId());
        $cart->setUser($user);

        $form = $this->createForm('AppBundle\Form\CartType', $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cart);
            $em->flush();

            return $this->redirectToRoute('cart_show', array('id' => $cart->getId()));
        }

        return $this->render('cart/newForAllProducts.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a cart entity.
     *
     * @Route("/", name="cart_show")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function showAction()
    {
        $userCurrency = $this->getUser()->getUserProfile()->getCurrency();
        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $this->getUser()->getId()]);

        $cartBill = $this->calculateCartBill();

        return $this->render('cart/show.html.twig', array(
            'addsInCart' => $addsInCart,
            'cartBill' => $cartBill,
            'userCurrency' => $userCurrency
        ));
    }

    /**
     *
     * @Route("/{id}/addProduct", name="cart_add_product")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function addProduct(Request $request, Product $product)
    {
        $addQuantity = $request->query->get('productQuantity');
        if ($addQuantity == '') {
            $addQuantity = 1;
        }
        $priceOrder = $product->getPrice() * $addQuantity;
        $user = $this->getUser();
        $currency = $product->getCurrency();

        if ($product->getUser()->getId() == $this->getUser()->getId()) {
            return $this->render('cart/buyOwnProduct.html.twig', [
            ]);
        }

        $em = $this->getDoctrine()->getManager();

        if ($product->getQuantity() < $addQuantity) {
            return $this->render('cart/QuantityShortage.html.twig', [
                'product' => $product
            ]);
        } else {
            $product->setQuantity($product->getQuantity() - $addQuantity);
            if ($product->getQuantity() == 0) {
                $product->setAvailability(0);
            }
            $em->persist($product);
            $em->flush();
        }

        $activePromotions = $this->findActivePromotions($product);

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

        return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
    }

    /**
     *
     * @Route("/buy", name="buy_product_cart")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function buy()
    {
        $cartBill = $this->calculateCartBill();
        $user = $this->getUser();
        $userCash = $user->getUserProfile()->getCash();

        if ($userCash >= $cartBill) {
            $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
            $userProfileRepo = $this->getDoctrine()->getRepository(UserProfile::class);
            $userProfile = $user->getUserProfile();

            $connection = $this->getDoctrine()->getConnection();
            $connection->beginTransaction();

            $cartRepo->buyProductsInCart($user->getId());

            $userProfile->setCash($userProfile->getCash() - $cartBill);
            $em = $this->getDoctrine()->getManager();
            $em->persist($userProfile);
            $em->flush();

            $this->buyAction();

            $connection->commit();

            return $this->render('cart/buySuccess.html.twig', [
                'cartBill' => $cartBill,
                'user' => $user
            ]);
        }
        $shortage = $cartBill - $userCash;

        return $this->render('cart/buyFailure.html.twig', [
            'shortage' => $shortage,
            'user' => $user
        ]);

    }

    private function buyAction()
    {
        $userId = $this->getUser()->getId();
        $userCurrency = $this->getUser()->getUserProfile()->getCurrency();
        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $userId]);

        $em = $this->getDoctrine()->getManager();

        foreach($addsInCart as $add) {
            if ($add->isBought() != 1 && $add->isRefused() != 1) {
                $product = $add->getProduct();
                $addQuantity = $add->getQuantity();
                $user = $this->getUser();

                $purchaseValue = $this->calculateAddInUserCurrency($add);
                $productRepo = $this->getDoctrine()->getRepository(Product::class);
                $addTotal = $product->getPrice() * $addQuantity;
                $productId = $product->getId();

                $em->persist($product);
                $em->flush();

                $userProfileSeller = $productRepo->find($productId)->getUser()->getUserProfile();
                $userProfileSeller->setCash($userProfileSeller->getCash() + $addTotal);
                $userProfileSeller->setSalesCount($userProfileSeller->getSalesCount() + 1);
                $userProfileSeller->setSalesValue($userProfileSeller->getSalesValue() + $purchaseValue);
                if ($userProfileSeller->getIsSeller() == 0) {
                    $userProfileSeller->setIsSeller(1);
                }
                $em->persist($userProfileSeller);
                $em->flush();

                $userProfileBuyer = $user->getUserProfile();
                $userProfileBuyer->setPurchaseCount($userProfileBuyer->getPurchaseCount() + 1);
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

                $userPurchaseRepo = $this->getDoctrine()->getRepository(UserPurchase::class);
            }
        }
    }

    /**
     * Deletes a cart entity.
     *
     * @Route("/{id}", name="refuse_row")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function refuseAction(Request $request, Cart $cart)
    {
        $product = $cart->getProduct();
        $addQuantity = $cart->getQuantity();
        $product->setQuantity($product->getQuantity() + $addQuantity);
        $em = $this->getDoctrine()->getManager();
        $em->persist($product);

        $connection = $this->getDoctrine()->getConnection();
        $connection->beginTransaction();
        $em->flush();

        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $cartRepo->refuseProduct($cart->getId());

        $connection->commit();
        return $this->redirectToRoute('cart_show');
    }

    private function calculateCartBill()
    {
        $userId = $this->getUser()->getId();
        $userCurrency = $this->getUser()->getUserProfile()->getCurrency();
        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $userId]);
        $cartBill = 0;

        foreach($addsInCart as $add) {
            if ($add->isBought() != 1 && $add->isRefused() != 1) {
                $priceAddInUserCurrency = $this->calculateAddInUserCurrency($add);
                $cartBill += $priceAddInUserCurrency;
            }
        }

        return $cartBill;
    }

    private function calculateAddInUserCurrency($add)
    {
        $userCurrency = $this->getUser()->getUserProfile()->getCurrency();
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

    private function findActivePromotions(Product $product)
    {
        $promotionRepo = $this->getDoctrine()->getRepository(Promotion::class);
        $productRepo = $this->getDoctrine()->getRepository(Product::class);
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
                    if (in_array($this->getUser()->getid(), $promoUsersIds)) {
                        $activePromotions[] = $promotion;
                    }
                    break;
            }
        }

        return $activePromotions;
    }

    private function getBestPromotion(array $promotions)
    {
        $bestPromotion = $promotions[0];
        foreach ($promotions as $promotion) {
            if ($promotion->getPercentsDiscount() > $bestPromotion->getPercentsDiscount()) {
                $bestPromotion = $promotion;
            }
        }
        return $bestPromotion;
    }

    private function calculateReduction(Product $product, int $percentsDiscount)
    {
        $productPrice = $product->getPrice();
        $reducedPrice = $productPrice - (($productPrice * $percentsDiscount) / 100);
        return number_format($reducedPrice, 2);
    }


}
