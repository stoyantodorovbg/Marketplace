<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

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
        $userId = $user = $this->getUser()->getId();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($userId);
        $userProfile = $user->getUserProfile();

        return $this->render('userprofile/show.html.twig', array(
            'userProfile' => $userProfile,
        ));
    }

    /**
     * Finds and displays a userProfile entity.
     *
     * @Route("/publicShow", name="userprofile_public_show")
     * @Method("GET")
     * @Security("is_granted(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'])")
     */
    public function publicShowAction()
    {
        $userId = $user = $this->getUser()->getId();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($userId);
        $userProfile = $user->getUserProfile();

        return $this->render('userprofile/public_show.html.twig', array(
            'userProfile' => $userProfile,
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
        $userId = $user = $this->getUser()->getId();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($userId);
        $userProfile = $user->getUserProfile();

        $editForm = $this->createForm('AppBundle\Form\UserProfileType', $userProfile);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('userprofile_show');
        }

        return $this->render('userprofile/edit.html.twig', array(
            'userProfile' => $userProfile,
            'edit_form' => $editForm->createView()
        ));
    }

}
