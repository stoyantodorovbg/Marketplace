<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class AdminController extends Controller
{
    /**
     * @Route("/adminAllUsersView", name="admin_all_users_view")
     * @Method("GET")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function allUsersView()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $allUsers = $userRepo->findAll();

        return $this->render('superAdmin/adminAllUsersView.html.twig',[
            'allUsers' => $allUsers
            ]);
    }

    /**
     * @Route("/adminUserView/{id})", name="admin_user_view")
     * @Method("GET")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function userView(User $user)
    {
        return $this->render('superAdmin/adminUserView.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/addEditor/{id})", name="add_editor")
     * @Method("GET")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function addEditor(User $user)
    {
        $roleRepo = $this->getDoctrine()->getRepository(Role::class);
        $roleEditor = $roleRepo->find(2);
        $user->addRole($roleEditor);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_user_view', [
            'id' => $user->getId()
        ]);
    }

    /**
     * @Route("/banEditor/{id})", name="ban_editor")
     * @Method("GET")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function banEditor(User $user)
    {
        $roleRepo = $this->getDoctrine()->getRepository(Role::class);
        $roleEditor = $roleRepo->find(2);
        $user->banRole($roleEditor);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_user_view', [
            'id' => $user->getId()
        ]);
    }

    /**
     * @Route("/banUser/{id})", name="ban_user")
     * @Method("GET")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function banUser(User $user)
    {
        $roleRepo = $this->getDoctrine()->getRepository(Role::class);
        $roleEditor = $roleRepo->find(1);
        $user->banRole($roleEditor);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_user_view', [
            'id' => $user->getId()
        ]);
    }

    /**
     * @Route("/returnUsersRights/{id})", name="return_users_rights")
     * @Method("GET")
     * @Security("is_granted(['ROLE_SUPER_ADMIN'])")
     */
    public function returnUsersRights(User $user)
    {
        $roleRepo = $this->getDoctrine()->getRepository(Role::class);
        $roleEditor = $roleRepo->find(1);
        $user->addRole($roleEditor);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_user_view', [
            'id' => $user->getId()
        ]);
    }
}
