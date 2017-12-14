<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * Promotion controller.
 *
 * @Route("promotion")
 */
class PromotionController extends Controller
{
    /**
     * Lists all promotion entities.
     *
     * @Route("/", name="promotion_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $promotions = $em->getRepository('AppBundle:Promotion')->findAll();

        return $this->render('promotion/index.html.twig', array(
            'promotions' => $promotions,
        ));
    }

    /**
     * @Route("/newChooseType", name="choose_type_new_promotion")
     */
    public function chooseTypeNewPromotion(Request $request)
    {
        if (isset($request->query->all()['choose-type'])) {
            $chosenType = $request->query->all()['choose-type'];

            switch ($chosenType) {
                case 'certain_products':
                    return $this->newForCertainProducts($request);
               case 'all_products':
                   return $this->newForAllProducts($request);
               case 'certain_categories':
                   return $this->newForCertainCategories($request);
               case 'certain_users':
                   return $this->redirectToRoute('choose_user_criteria');
            }
        }

        return $this->render('promotion/new_promotion_choose_type.html.twig');
    }


    /**
     * @Route("/newForCertainProducts", name="new_for_certain_products")
     */
    private function newForCertainProducts(Request $request)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $allUsers = $userRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setType('certain_products');

        $form = $this->createForm('AppBundle\Form\PromotionType', $promotion);
        $form->add('products', EntityType::class, [
            'class' => 'AppBundle:Product',
            'choice_label' => 'name',
            'placeholder' => 'choose products',
            'multiple' => true,
            'expanded' => true,
            'label' => ' '
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();

            return $this->redirectToRoute('promotion_show', array('id' => $promotion->getId()));
        }

        return $this->render('promotion/newForCertainProducts.html.twig', array(
            'promotion' => $promotion,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/newForAllProducts", name="new_for_all_products")
     */
    private function newForAllProducts(Request $request)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $allUsers = $userRepo->findAll();
        $productRepo = $this->getDoctrine()->getRepository(Product::class);
        $allProducts = $productRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setProducts($allProducts);
        $promotion->setType('all_products');

        $form = $this->createForm('AppBundle\Form\PromotionType', $promotion);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();

            return $this->redirectToRoute('promotion_show', array('id' => $promotion->getId()));
        }

        return $this->render('promotion/newForAllProducts.html.twig', array(
            'promotion' => $promotion,
            'form' => $form->createView(),
        ));
    }


    /**
     * @Route("/newForCertainCategories", name="new_for_certain_categories")
     */
    private function newForCertainCategories(Request $request)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $allUsers = $userRepo->findAll();

        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($allUsers);
        $promotion->setType('certain_categories');

        $form = $this->createForm('AppBundle\Form\PromotionType', $promotion);
        $form->add('categories', EntityType::class, [
            'class' => 'AppBundle:Category',
            'choice_label' => 'name',
            'placeholder' => 'choose category',
            'multiple' => true,
            'expanded' => true,
            'label' => ' '
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();

            return $this->redirectToRoute('promotion_show', array('id' => $promotion->getId()));
        }

        return $this->render('promotion/newForCertainCategories.html.twig', array(
            'promotion' => $promotion,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/chooseUserCriteria", name="choose_user_criteria")
     * @Method("GET")
     */
    public function chooseUserCriteria(Request $request) {
        if (isset($request->query->all()['criteria'])) {
            $criteria = $request->query->all()['criteria'];

            switch ($criteria) {
                case 'rating':
                    return $this->findUsersByRating($request);
                case 'purchasesValue':
                    return $this->findUsersByPurchaseValue($request);
                case 'purchasesCount':
                    return $this->findUsersByPurchaseCount($request);
                case 'cash':
                    return $this->findUsersByCash($request);
                case 'registrationDate':
                    return $this->findUsersByRegistrationDate($request);
            }
        }
        return $this->render('promotion/chooseUserCriteria.html.twig');
    }

    /**
     * @Route("/setRating", name="set_rating")
     * @Method({"GET", "POST"})
     */
    public function findUsersByRating(Request $request)
    {
        if (isset($request->query->all()['min_rating'])) {
            $minRating = $request->query->all()['min_rating'];
            $userProfiles = $this
                ->getDoctrine()
                ->getRepository(Promotion::class)
                ->findUserByPurchaseValue($minRating);
            $users = $this->findUsersByUserProfiles($userProfiles);
            return $this->newForCertainUsers($request, $users);

        }
        return $this->render('promotion/setRating.html.twig');
    }

    /**
     * @Route("/setPurchaseValue", name="set_purchase_value")
     * @Method({"GET", "POST"})
     */
    public function findUsersByPurchaseValue(Request $request)
    {
        if (isset($request->query->all()['min_purchases_value'])) {
            $minPurchasesValue = $request->query->all()['min_purchases_value'];
            $userProfiles = $this
                ->getDoctrine()
                ->getRepository(Promotion::class)
                ->findUserByPurchaseValue($minPurchasesValue);
            $users = $this->findUsersByUserProfiles($userProfiles);
            return $this->newForCertainUsers($request, $users);

        }
        return $this->render('promotion/setPurchaseValue.html.twig');
    }

    /**
     * @Route("/setPurchaseCount", name="set_purchase_count")
     * @Method({"GET", "POST"})
     */
    public function findUsersByPurchaseCount(Request $request)
    {
        if (isset($request->query->all()['min_purchases_count'])) {
            $minPurchaseCount = $request->query->all()['min_purchases_count'];
            $userProfiles = $this
                ->getDoctrine()
                ->getRepository(Promotion::class)
                ->findUserByPurchaseCount($minPurchaseCount);
            $users = $this->findUsersByUserProfiles($userProfiles);
            return $this->newForCertainUsers($request, $users);

        }
        return $this->render('promotion/setPurchaseCount.html.twig');
    }

    /**
     * @Route("/setCash", name="set_cash")
     * @Method({"GET", "POST"})
     */
    public function findUsersByCash(Request $request)
    {
        if (isset($request->query->all()['min_cash'])) {
            $minCash = $request->query->all()['min_cash'];
            $userProfiles = $this
                ->getDoctrine()
                ->getRepository(Promotion::class)
                ->findUserByCash($minCash);
            $users = $this->findUsersByUserProfiles($userProfiles);
            return $this->newForCertainUsers($request, $users);

        }
        return $this->render('promotion/setCash.html.twig');
    }

    /**
     * @Route("/setRegistrationDate", name="set_registration_date")
     * @Method({"GET", "POST"})
     */
    public function findUsersByRegistrationDate(Request $request)
    {
        if (isset($request->query->all()['most_recent_date'])) {
            $mostRecentDate = $request->query->all()['most_recent_date'];
            $users = $this
                ->getDoctrine()
                ->getRepository(Promotion::class)
                ->findUserByDateCreated($mostRecentDate);
            return $this->newForCertainUsers($request, $users);

        }
        return $this->render('promotion/setRegistrationDate.html.twig');
    }

    private function findUsersByUserProfiles($userProfiles) {
        $users = [];
        foreach ($userProfiles as $userProfile) {
            $userProfileId = $userProfile->getId();
            $userRepo = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepo->findOneBy(['userProfile' => $userProfile]);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @Route("/newForCertainUsers", name="new_for_certain_users")
     * @Method("GET")
     */
    private function newForCertainUsers(Request $request, $users)
    {
        $promotion = new Promotion();
        $promotion->setCreatedDate(new \DateTime());
        $promotion->setUsers($users);
        $promotion->setType('certain_users');
        $form = $this->createForm('AppBundle\Form\PromotionType', $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($promotion);
            $em->flush();

            return $this->redirectToRoute('promotion_show', array('id' => $promotion->getId()));
        }

        return $this->render('promotion/newForCertainUsers.html.twig', array(
            'promotion' => $promotion,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a promotion entity.
     *
     * @Route("/{id}", name="promotion_show")
     * @Method("GET")
     */
    public function showAction(Promotion $promotion)
    {
        $deleteForm = $this->createDeleteForm($promotion);

        return $this->render('promotion/show.html.twig', array(
            'promotion' => $promotion,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing promotion entity.
     *
     * @Route("/{id}/edit", name="promotion_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Promotion $promotion)
    {
        $deleteForm = $this->createDeleteForm($promotion);
        $editForm = $this->createForm('AppBundle\Form\PromotionType', $promotion);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('promotion_edit', array('id' => $promotion->getId()));
        }

        return $this->render('promotion/edit.html.twig', array(
            'promotion' => $promotion,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a promotion entity.
     *
     * @Route("/{id}", name="promotion_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Promotion $promotion)
    {
        $form = $this->createDeleteForm($promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($promotion);
            $em->flush();
        }

        return $this->redirectToRoute('promotion_index');
    }

    /**
     * Creates a form to delete a promotion entity.
     *
     * @param Promotion $promotion The promotion entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Promotion $promotion)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('promotion_delete', array('id' => $promotion->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
