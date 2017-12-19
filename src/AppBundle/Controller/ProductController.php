<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Service\ProductService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product controller.
 *
 * @Route("product")
 */
class ProductController extends Controller
{
    /**
     * List available products
     * @Route("/", name="product_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        //$products = $em->getRepository('AppBundle:Product')->findAll();
        $products = $em->getRepository('AppBundle:Product')->findByPriority();

        return $this->render('product/index.html.twig', array(
            'products' => $products,
        ));
    }

    /**
     * List all products
     * @Route("/allProducts", name="all_products_admin")
     * @Method("GET")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function showAllProductsAdmin()
    {
        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository('AppBundle:Product')->findAll();

        return $this->render('product/allProductsAdmin.html.twig', array(
            'products' => $products,
        ));
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="product_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $userCurrency = $user->getUserProfile()->getCurrency();
        $product = new Product();
        $product->setUser($user);
        $product->setPriority(0);
        $product->setCurrency($userCurrency);
        $form = $this->createForm('AppBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantity = $request->request->all()['appbundle_product']['quantity'];
            if (intval($quantity) > 0) {
                $product->setAvailability(1);
            } else {
                $product->setAvailability(0);
            }

            $image = $product->getImage();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $image->move(
                $this->getParameter('images_directory'),
                $imageName
            );
            $product->setImage($imageName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_show', array('id' => $product->getId()));
        }

        return $this->render('product/new.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/{id}", name="product_show")
     * @Method("GET")
     */
    public function showAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);
        $categories = $product->getCategories();

        $productService = $this->get(ProductService::class);
        $activePromotions = $productService->findActivePromotions($product, $this->getUser());

        $bestPromotion = false;
        $reducedPrice = false;
        if (count($activePromotions) > 0) {
            $bestPromotion = $productService->getBestPromotion($activePromotions);
            $reducedPrice = $productService->calculateReduction($product, $bestPromotion->getpercentsDiscount());
        }


        return $this->render('product/show.html.twig', array(
            'product' => $product,
            'delete_form' => $deleteForm->createView(),
            'bestPromotion' => $bestPromotion,
            'reducedPrice' => $reducedPrice
        ));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request, Product $product)
    {
        $imageName = null;
        if($product->getImage() != null) {
            $imageName = $product->getImage();
            $product->setImage(new File($this->getParameter('images_directory').'/'.$product->getImage()));
        }

        $deleteForm = $this->createDeleteForm($product);
        $editForm = $this->createForm('AppBundle\Form\ProductType', $product);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $quantity = $request->request->all()['appbundle_product']['quantity'];
            if (intval($quantity) > 0) {
                $product->setAvailability(1);
            } else {
                $product->setAvailability(0);
            }

            if ($editForm->all()['image']->getNormData() == '') {
                $product->setImage($imageName
                );
            } else {
                $image = $product->getImage();
                $imageName = md5(uniqid()).'.'.$image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $imageName
                );
                $product->setImage($imageName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_show', array('id' => $product->getId()));
        }

        return $this->render('product/edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/removeFromSale/{id}", name="remove_from_sale")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function removeFromSale(Product $product)
    {
        $productService = $this->get(ProductService::class);
        $productService->removeFromSale($product);

        return $this->redirectToRoute('homepage');
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        $productService = $this->get(ProductService::class);
        $productService->delete($product, $form);

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
