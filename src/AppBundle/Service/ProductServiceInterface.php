<?php


namespace AppBundle\Service;


use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use Symfony\Component\Form\Form;

interface ProductServiceInterface
{
    public function removeFromSale(Product $product);

    public function delete(Product $product, Form $form);

    public function findActivePromotions(Product $product, User $user):array;

    public function getBestPromotion(array $promotions):array;

    public function calculateReduction(Product $product, int $percentsDiscount);

}