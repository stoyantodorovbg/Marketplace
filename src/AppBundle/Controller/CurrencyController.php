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
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
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

        return $this->render('currency/show.html.twig', array(
            'currency' => $currency,
        ));
    }

    /**
     * Displays a form to edit an existing currency entity.
     *
     * @Route("/{id}/edit", name="currency_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request, Currency $currency)
    {
        $editForm = $this->createForm('AppBundle\Form\CurrencyType', $currency);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('currency_edit', array('id' => $currency->getId()));
        }

        return $this->render('currency/edit.html.twig', array(
            'currency' => $currency,
            'edit_form' => $editForm->createView()
        ));
    }
}
