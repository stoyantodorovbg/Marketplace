<?php


namespace AppBundle\Service;


use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Entity\UserPurchase;

interface UserProfileServiceInterface
{
    public function putPurchaseOnSale(Product $product, User $user, UserPurchase $userPurchase);
}