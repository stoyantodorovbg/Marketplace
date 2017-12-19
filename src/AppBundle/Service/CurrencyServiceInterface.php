<?php


namespace AppBundle\Service;


use AppBundle\Entity\Currency;
use Symfony\Component\Form\Form;

interface CurrencyServiceInterface
{
    public function newAction(Form $form, Currency $currency):bool;

    public function deleteAction(Form $form, Currency $currency);
}