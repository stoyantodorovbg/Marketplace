<?php


namespace AppBundle\Service;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Currency;
use AppBundle\Entity\Product;
use AppBundle\Entity\Promotion;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface CartServiceInterface
{
    public function addProduct(Product $product, User $user, int $addQuantity, Currency $currency, float $priceOrder);

    public function buyAction($user);

    public function refuse(Request $request, Cart $cart);

    public function return(Request $request, Cart $cart);

    public function calculateCartBill(User $user);

    public function calculateAddInUserCurrency(Cart $add, User $user);

    public function findActivePromotions(Product $product, User $user);

    public function getBestPromotion(array $promotions);

    public function calculateReduction(Product $product, int $percentsDiscount);
}