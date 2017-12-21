<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Instruction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Instruction controller.
 *
 * @Route("instruction")
 */
class InstructionController extends Controller
{
    /**
     * Lists all instruction entities.
     *
     * @Route("/", name="instruction_index")
     * @Method("GET")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $instructions = $em->getRepository('AppBundle:Instruction')->findAll();

        return $this->render('instruction/index.html.twig', array(
            'instructions' => $instructions,
        ));
    }

    /**
     * Creates a new instruction entity.
     *
     * @Route("/new", name="instruction_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function newAction(Request $request)
    {
        $instruction = new Instruction();
        $form = $this->createForm('AppBundle\Form\InstructionType', $instruction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($instruction);
            $em->flush();

            return $this->redirectToRoute('instruction_show', array('id' => $instruction->getId()));
        }

        return $this->render('instruction/new.html.twig', array(
            'instruction' => $instruction,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a instruction entity.
     *
     * @Route("/{id}", name="instruction_show")
     * @Method("GET")
     */
    public function showAction(Instruction $instruction)
    {
        $deleteForm = $this->createDeleteForm($instruction);

        return $this->render('instruction/show.html.twig', array(
            'instruction' => $instruction,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing instruction entity.
     *
     * @Route("/{id}/edit", name="instruction_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request, Instruction $instruction)
    {
        $deleteForm = $this->createDeleteForm($instruction);
        $editForm = $this->createForm('AppBundle\Form\InstructionType', $instruction);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('instruction_edit', array('id' => $instruction->getId()));
        }

        return $this->render('instruction/edit.html.twig', array(
            'instruction' => $instruction,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a instruction entity.
     *
     * @Route("/{id}", name="instruction_delete")
     * @Method("DELETE")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function deleteAction(Request $request, Instruction $instruction)
    {
        $form = $this->createDeleteForm($instruction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($instruction);
            $em->flush();
        }

        return $this->redirectToRoute('instruction_index');
    }

    /**
     * Creates a form to delete a instruction entity.
     *
     * @param Instruction $instruction The instruction entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Instruction $instruction)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('instruction_delete', array('id' => $instruction->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
