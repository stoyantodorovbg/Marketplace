<?php


namespace AppBundle\Service;


use AppBundle\Entity\Category;
use Symfony\Component\Form\Form;

interface CategoryServiceInterface
{
    public function newAction(Form $form, Category $category):bool;

    public function showAction(Category $category);

    public function deleteAction(Form $form, Category $category):bool;
}