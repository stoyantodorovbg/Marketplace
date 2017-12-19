<?php


namespace AppBundle\Service;


use AppBundle\Entity\Currency;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;

class CurrencyService implements CurrencyServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function newAction(Form $form, Currency $currency):bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->persist($currency);
            $em->flush();

            return true;
        }

        return false;
    }

    public function deleteAction(Form $form, Currency $currency)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->remove($currency);
            $em->flush();
        }
    }

}