<?php


namespace AppBundle\Service;


use AppBundle\Entity\User;
use Symfony\Component\Form\Form;

interface UserServiceInterface
{
    public function register(User $user, Form $form, $password);
}