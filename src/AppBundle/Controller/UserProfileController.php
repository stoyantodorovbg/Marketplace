<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Entity\UserPurchase;
use AppBundle\Service\UserProfileService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Userprofile controller.
 *
 * @Route("userprofile")
 */
class UserProfileController extends Controller
{
    /**
     * Lists all userProfile entities.
     *
     * @Route("/", name="userprofile_index")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userProfiles = $em->getRepository('AppBundle:UserProfile')->findAll();

        return $this->render('userprofile/index.html.twig', array(
            'userProfiles' => $userProfiles,
        ));
    }

    /**
     * Finds and displays a userProfile entity.
     *
     * @Route("/show", name="userprofile_show")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function showAction()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $this->getUser();
        $userProfile = $user->getUserProfile();

        return $this->render('userprofile/show.html.twig', array(
            'userProfile' => $userProfile,
        ));
    }

    /**
     * Finds and displays a userProfile entity.
     *
     * @Route("/publicShow{id}", name="userprofile_public_show")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function publicShowAction(UserProfile $userProfile)
    {
        $userProfileRepo = $this->getDoctrine()->getRepository(UserProfile::class);
        $userProfile = $userProfileRepo->find($userProfile->getId());

        $userPurchases = $userProfile->getUser()->getPurchases();

        return $this->render('userprofile/public_show.html.twig', array(
            'userProfile' => $userProfile,
            'userPurchases' => $userPurchases
        ));
    }

    /**
     * @Route("/userPurchasesView)", name="user_purchases_view")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function userPurchasesView()
    {
        $user = $this->getUser();
        $productRepo = $this->getDoctrine()->getRepository(Product::class);
        $userProducts = $productRepo->findBy(['user' => $user]);

        $userPurchases = $user->getPurchases();

        return $this->render('userprofile/userPurchasesView.html.twig', [
            'user' => $user,
            'userPurchases' => $userPurchases
        ]);
    }

    /**
     * @Route("/putPurchaseOnSale/{id})", name="put_purchase_on_sale")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function putPurchaseOnSale(Request $request, UserPurchase $userPurchase)
    {
        $user = $this->getUser();

        $product = new Product();
        $form = $this->createForm('AppBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {//&& $form->isValid()
            $userProfileService = $this->get(UserProfileService::class);
            $userProfileService->putPurchaseOnSale($product, $user, $userPurchase);

            return $this->redirectToRoute('product_show', array('id' => $product->getId()));
        }

        return $this->render('userprofile/putPurchaseOnSale.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing userProfile entity.
     *
     * @Route("/edit", name="userprofile_edit")
     * @Method({"GET", "POST"})
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function editAction(Request $request)
    {
        $userId = $this->getUser()->getId();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($userId);
        $userProfile = $user->getUserProfile();

        $imageName = null;
        if($userProfile->getImage() != null) {
            $imageName = $userProfile->getImage();
            $userProfile->setImage(new File($this->getParameter('images_directory').'/'.$userProfile->getImage()));
        }

        $editForm = $this->createForm('AppBundle\Form\UserProfileType', $userProfile);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($editForm->all()['image']->getNormData() == '') {
                $userProfile->setImage($imageName
                );
            } else {
                $image = $userProfile->getImage();
                $imageName = md5(uniqid()).'.'.$image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $imageName
                );
                $userProfile->setImage($imageName);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('userprofile_show');
        }

        return $this->render('userprofile/edit.html.twig', array(
            'userProfile' => $userProfile,
            'edit_form' => $editForm->createView()
        ));
    }

}
