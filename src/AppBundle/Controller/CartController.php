<?php
namespace AppBundle\Controller;
use AppBundle\Entity\Cart;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Service\CartService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
        $user = $this->getUser();
        $cartService = $this->get(CartService::class);
        $userCurrency = $user->getUserProfile()->getCurrency();
        $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
        $addsInCart = $cartRepo->findBy(['user' => $this->getUser()->getId()]);
        $cartBill = $cartService->calculateCartBill($user);

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

        $currency = $this->getUser()->getUserProfile()->getCurrency();
        $cartService = $this->get(CartService::class);
        $cartService->addProduct( $product, $user, $addQuantity, $currency, $priceOrder);

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
        $cartService = $this->get(CartService::class);
        $cartBill = $cartService->calculateCartBill($this->getUser());
        $user = $this->getUser();
        $userCash = $user->getUserProfile()->getCash();

        if ($userCash >= $cartBill) {
            $cartRepo = $this->getDoctrine()->getRepository(Cart::class);
            $userProfileRepo = $this->getDoctrine()->getRepository(UserProfile::class);
            $userProfile = $user->getUserProfile();

            $connection = $this->getDoctrine()->getConnection();
            $connection->beginTransaction();

            $userProfile->setCash($userProfile->getCash() - $cartBill);
            $em = $this->getDoctrine()->getManager();
            $em->persist($userProfile);
            $em->flush();

            $cartService = $this->get(CartService::class);
            $cartService->buyAction($user);

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

    /**
     * @Route("/{id}", name="return_refuse_row")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function returnRefuseAction(Request $request, Cart $cart)
    {
        $cartService = $this->get(CartService::class);
        $isRefused = $cart->isRefused();
        if($isRefused == 0) {
            $cartService->refuse($request, $cart);
            return $this->redirectToRoute('cart_show');
        } else {
            $cartService->return($request, $cart);
            return $this->redirectToRoute('cart_show');
        }
    }
}