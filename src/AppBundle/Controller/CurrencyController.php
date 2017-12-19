<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Currency;
use AppBundle\Service\CurrencyService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Currency controller.
 *
 * @Route("currency")
 */
class CurrencyController extends Controller
{
    /**
     * Lists all currency entities.
     *
     * @Route("/", name="currency_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $currencies = $em->getRepository('AppBundle:Currency')->findAll();

        return $this->render('currency/index.html.twig', array(
            'currencies' => $currencies,
        ));
    }

    /**
     * Creates a new currency entity.
     *
     * @Route("/new", name="currency_new")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function newAction(Request $request)
    {
        $currency = new Currency();
        $form = $this->createForm('AppBundle\Form\CurrencyType', $currency);
        $form->handleRequest($request);

        $currencyService = $this->get(CurrencyService::class);
        if ($currencyService->newAction($form, $currency)) {
            return $this->redirectToRoute('currency_show', array('id' => $currency->getId()));
        }

        return $this->render('currency/new.html.twig', array(
            'currency' => $currency,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a currency entity.
     *
     * @Route("/{id}", name="currency_show")
     * @Method("GET")
     */
    public function showAction(Currency $currency)
    {
        $deleteForm = $this->createDeleteForm($currency);

        return $this->render('currency/show.html.twig', array(
            'currency' => $currency,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing currency entity.
     *
     * @Route("/{id}/edit", name="currency_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request, Currency $currency)
    {
        $deleteForm = $this->createDeleteForm($currency);
        $editForm = $this->createForm('AppBundle\Form\CurrencyType', $currency);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('currency_edit', array('id' => $currency->getId()));
        }

        return $this->render('currency/edit.html.twig', array(
            'currency' => $currency,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a currency entity.
     *
     * @Route("/{id}", name="currency_delete")
     * @Method("DELETE")
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function deleteAction(Request $request, Currency $currency)
    {
        $form = $this->createDeleteForm($currency);
        $form->handleRequest($request);

        $currencyService = $this->get(CurrencyService::class);
        $currencyService->deleteAction($form, $currency);

        return $this->redirectToRoute('currency_index');
    }

    /**
     * Creates a form to delete a currency entity.
     *
     * @param Currency $currency The currency entity
     *
     * @return \Symfony\Component\Form\Form The form
     * @Security("is_granted(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    private function createDeleteForm(Currency $currency)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('currency_delete', array('id' => $currency->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
