<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
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
     * @Route("/{id}", name="cart_show")
     * @Method("GET")
     */
    public function showAction(Cart $cart)
    {
        $deleteForm = $this->createDeleteForm($cart);

        return $this->render('cart/show.html.twig', array(
            'cart' => $cart,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     *
     * @Route("/{id}/addProduct", name="cart_add_product")
     * @Method({"GET", "POST"})
     */
    public function addProduct(Request $request, Product $product)
    {
        $user = $this->getUser();

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setProduct($product);
        $cart->setQuantity(1);
        //$cart->setStatus(1); //ne e kupeno

        $em = $this->getDoctrine()->getManager();//
        $em->persist($cart);
        $em->flush();

        return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
    }

    /**
     * Displays a form to edit an existing cart entity.
     *
     * @Route("/{id}/edit", name="cart_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Cart $cart)
    {
        $deleteForm = $this->createDeleteForm($cart);
        $editForm = $this->createForm('AppBundle\Form\CartType', $cart);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cart_edit', array('id' => $cart->getId()));
        }

        return $this->render('cart/edit.html.twig', array(
            'cart' => $cart,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a cart entity.
     *
     * @Route("/{id}", name="cart_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Cart $cart)
    {
        $form = $this->createDeleteForm($cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($cart);
            $em->flush();
        }

        return $this->redirectToRoute('cart_index');
    }

    /**
     * Creates a form to delete a cart entity.
     *
     * @param Cart $cart The cart entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Cart $cart)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_delete', array('id' => $cart->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
