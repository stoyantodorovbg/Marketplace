<?php


namespace AppBundle\Service;


use AppBundle\Entity\Promotion;

interface PromotionServiceInterface
{
    public function newForCertainProducts():Promotion;

    public function newForAllUsers():Promotion;

    public function newForCertainCategories():Promotion;

    public function findUsersByUserProfiles($userProfiles):array;

    public function newForCertainUsers($users):Promotion;

}