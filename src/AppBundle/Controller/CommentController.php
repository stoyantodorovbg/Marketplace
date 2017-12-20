<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Product;
use AppBundle\Service\CommentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Comment controller.
 *
 * @Route("comment")
 */
class CommentController extends Controller
{
    /**
     * Lists all comment entities.
     *
     * @Route("/", name="comment_index")
     * @Method("GET")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository('AppBundle:Comment')->findAll();

        return $this->render('comment/index.html.twig', array(
            'comments' => $comments,
        ));
    }

    /**
     * Creates a new comment entity.
     *
     * @Route("/new/{id}", name="comment_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function newAction(Request $request, Product $product)
    {
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setProduct($product);
        $form = $this->createForm('AppBundle\Form\CommentType', $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $commentService = $this->get(CommentService::class);
            $commentService->newAction($user, $comment);

            return $this->redirectToRoute('product_show', array('id' => $product->getId()));
        }

        return $this->render('comment/new.html.twig', array(
            'comment' => $comment,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing comment entity.
     *
     * @Route("/{id}/edit", name="comment_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request, Comment $comment)
    {
        $deleteForm = $this->createDeleteForm($comment);
        $editForm = $this->createForm('AppBundle\Form\CommentType', $comment);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_show', array('id' => $comment->getProduct()->getId()));
        }

        return $this->render('comment/edit.html.twig', array(
            'comment' => $comment,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a comment entity.
     *
     * @Route("/{id}", name="comment_delete")
     * @Method("DELETE")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function deleteAction(Request $request, Comment $comment)
    {
        $productId = $comment->getProduct()->getId();

        $form = $this->createDeleteForm($comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentService = $this->get(CommentService::class);
            $commentService->deleteAction($comment, $this->getUser());
        }

        return $this->redirectToRoute('product_show', array('id' => $productId));
    }

    /**
     * Creates a form to delete a comment entity.
     *
     * @param Comment $comment The comment entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Comment $comment)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('comment_delete', array('id' => $comment->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
