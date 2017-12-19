<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Service\CategoryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Category controller.
 *
 * @Route("category")
 */
class CategoryController extends Controller
{
    /**
     * Lists all category entities.
     *
     * @Route("/", name="category_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('AppBundle:Category')->findAll();

        return $this->render('category/index.html.twig', array(
            'categories' => $categories
        ));
    }

    /**
     * Creates a new category entity.
     *
     * @Route("/new", name="category_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function newAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm('AppBundle\Form\CategoryType', $category);
        $form->handleRequest($request);

        $categoryService = $this->get(CategoryService::class);

        if ($categoryService->newAction($form, $category)) {
            return $this->redirectToRoute('category_show', array('id' => $category->getId()));
        }

        return $this->render('category/new.html.twig', array(
            'category' => $category,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a category entity.
     *
     * @Route("/{id}", name="category_show")
     * @Method("GET")
     */
    public function showAction(Category $category)
    {
        $deleteForm = $this->createDeleteForm($category);
        $categoryService = $this->get(CategoryService::class);
        $allCategories = $categoryService->showAction($category);

        return $this->render('category/show.html.twig', array(
            'category' => $category,
            'delete_form' => $deleteForm->createView(),
            'allCategories' =>$allCategories
        ));
    }

    /**
     * Displays a form to edit an existing category entity.
     *
     * @Route("/{id}/edit", name="category_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request, Category $category)
    {
        $deleteForm = $this->createDeleteForm($category);
        $editForm = $this->createForm('AppBundle\Form\CategoryType', $category);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('category_edit', array('id' => $category->getId()));
        }

        return $this->render('category/edit.html.twig', array(
            'category' => $category,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a category entity.
     *
     * @Route("/{id}", name="category_delete")
     * @Method("DELETE")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function deleteAction(Request $request, Category $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        $categoryService = $this->get(CategoryService::class);
        $categoryService->deleteAction($form, $category);

        return $this->redirectToRoute('category_index');
    }

    /**
     * Creates a form to delete a category entity.
     *
     * @param Category $category The category entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category_delete', array('id' => $category->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
