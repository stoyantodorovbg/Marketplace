<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        $products = $em->getRepository('AppBundle:Product')->findAll();

        return $this->render('product/index.html.twig', array(
            'products' => $products,
        ));
    }

    /**
     * List all products
     * @Route("/allProducts", name="all_products_admin")
     * @Method("GET")
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
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $userCurrency = $user->getUserProfile()->getCurrency();
        $product = new Product();
        $product->setUser($user);
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

        $activePromotions = $this->findActivePromotions($product);

        $bestPromotion = false;
        $reducedPrice = false;
        if (count($activePromotions) > 0) {
            $bestPromotion = $this->getBestPromotion($activePromotions);
            $reducedPrice = $this->calculateReduction($product, $bestPromotion->getpercentsDiscount());
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
     */
    public function editAction(Request $request, Product $product)
    {
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
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_edit', array('id' => $product->getId()));
        }

        return $this->render('product/edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm();
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
