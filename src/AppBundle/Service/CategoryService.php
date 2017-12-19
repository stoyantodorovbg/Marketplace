<?php


namespace AppBundle\Service;


use AppBundle\Entity\Category;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

class CategoryService implements CategoryServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function newAction(Form $form, Category $category):bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->persist($category);
            $em->flush();

            return true;
        }

        return false;
    }

    public function showAction(Category $category)
    {
        $categoryRepo = $this->entityManager->getRepository(Category::class);
        $allCategories = false;
        $categories = $categoryRepo->findAll();
        foreach($categories as $cat) {
            if ($cat->getParent() != null && $cat->getParent() == $category) {
                $allCategories = true;
            }
        }
        if ($allCategories) {
            $allCategories = $categories;
        }
        return $allCategories;
    }

    public function deleteAction(Form $form, Category $category):bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->remove($category);
            $em->flush();

            return true;
        }
        return false;
    }

}