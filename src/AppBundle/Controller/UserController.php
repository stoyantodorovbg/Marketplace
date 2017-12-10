<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/register", name="register_user")
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $user = new User();
        $user->setDateCreated(new \DateTime('now'));
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()){
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->GetPlainPassword());

            $user->setPassword($password);

            $roleRepository = $this->getDoctrine()->getRepository(Role::class);
            $userRepository = $this->getDoctrine()->getRepository(User::class);

            $userRole = $roleRepository->findOneBy(['name' => 'ROLE_USER']);

            $userProfile = new UserProfile();

            $user->addRole($userRole);
            $user->addUserProfile($userProfile);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("security_login");
        }
        return $this->render("user/register.html.twig", [
            'form' => $form->createView()
        ]);
    }
    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/edit", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request)
    {
        $userId = $user = $this->getUser()->getId();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($userId);

        $editForm = $this->createForm('AppBundle\Form\UserType', $user);
        $editForm->handleRequest($request);
        //dump($editForm);exit;

        if ($editForm->isSubmitted() ) {//&& $editForm->isValid()
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_show');
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Finds and displays a userProfile entity.
     *
     * @Route("/show", name="user_show")
     * @Method("GET")
     */
    public function showAction()
    {
        $userId = $user = $this->getUser()->getId();
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($userId);

        return $this->render('user/show.html.twig', array(
            'user' => $user,
        ));
    }

}
