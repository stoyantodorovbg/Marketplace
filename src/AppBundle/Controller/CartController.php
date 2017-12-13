<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

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

        return $this->render('cart/new.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a cart entity.
     *
     * @Route("/", name="cart_show")
     * @Method("GET")
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
     */
    public function addProduct(Request $request, Product $product)
    {
        $productQuantity = $request->query->get('productQuantity');
        $priceOrder = $product->getPrice() * $productQuantity;
        $user = $this->getUser();
        $currency = $product->getCurrency();

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setProduct($product);
        $cart->setPrice($priceOrder);
        $cart->setCurrency($currency);
        $cart->setBought(0); // is not bought
        $cart->setRefused(0); // is not refused
        $cart->setQuantity($productQuantity);


        $em = $this->getDoctrine()->getManager();
        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
    }

    /**
     *
     * @Route("/buy", name="buy_product_cart")
     * @Method("GET")
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

            $cartRepo->buyProductsInCart($user->getId());
            $userProfile->setCash($userProfile->getCash() - $cartBill);
            $em = $this->getDoctrine()->getManager();
            $em->persist($userProfile);
            $em->flush();

            $this->chargeBuyersCash();

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

    /**
     * Displays a form to edit an existing cart entity.
     *
     * @Route("/{id}/edit", name="cart_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Cart $cart)
    {
        $editForm = $this->createForm('AppBundle\Form\CartType', $cart);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cart_edit', array('id' => $cart->getId()));
        }

        return $this->render('cart/edit.html.twig', array(
            'cart' => $cart,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a cart entity.
     *
     * @Route("/{id}", name="refuse_row")
     * @Method("GET")
     */
    public function refuseAction(Request $request, Cart $cart)
    {
        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $cartRepo->refuseProduct($cart->getId());
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
                $priceAddInEuro = 0;
                if ($add->getCurrency()->getExchangeRateEUR() < 1) {
                    $priceAddInEuro = $add->getPrice() * $add->getCurrency()->getExchangeRateEUR();
                } elseif ($add->getCurrency()->getExchangeRateEUR() > 1) {
                    $priceAddInEuro = $add->getPrice() / $add->getCurrency()->getExchangeRateEUR();
                } else {
                    $priceAddInEuro = $add->getPrice();
                }

                $priceAddInUserCurrency = 0;
                if ($userCurrency->getExchangeRateEUR() < 1) {
                    $priceAddInUserCurrency = $priceAddInEuro / $userCurrency->getExchangeRateEUR();
                } elseif ($userCurrency->getExchangeRateEUR() > 1) {
                    $priceAddInUserCurrency = $priceAddInEuro * $userCurrency->getExchangeRateEUR();
                } else {
                    $priceAddInUserCurrency = $priceAddInEuro;
                }
                $cartBill += $priceAddInUserCurrency;
            }
        }

        return number_format($cartBill, 2);
    }

    private function chargeBuyersCash()
    {
        $userId = $this->getUser()->getId();
        $userCurrency = $this->getUser()->getUserProfile()->getCurrency();
        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $userId]);

        foreach($addsInCart as $add) {
            if ($add->isBought() != 1 && $add->isRefused() != 1) {
                $productRepo = $this->getDoctrine()->getRepository(Product::class);
                $addTotal = $add->getProduct()->getPrice() * $add->getQuantity();
                $productId = $add->getProduct()->getId();
                $userProfile = $productRepo->find($productId)->getUser()->getUserProfile();
                $userProfile->setCash($userProfile->getCash() + $addTotal);
                $em = $this->getDoctrine()->getManager();
                $em->persist($userProfile);
                $em->flush();
            }
        }
    }

}
