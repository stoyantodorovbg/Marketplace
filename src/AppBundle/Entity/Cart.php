<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CartRepository")
 */
class Cart
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;


    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="bought", type="boolean")
     */
    private $bought;

    /**
     * @var bool
     *
     * @ORM\Column(name="refused", type="boolean")
     */
    private $refused;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_in_promotion", type="boolean")
     */
    private $isInPromotion;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="carts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="Currency", inversedBy="carts")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="carts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")")
     */
    private $user;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return bool
     */
    public function isBought()
    {
        return $this->bought;
    }

    /**
     * @param bool $bought
     */
    public function setBought($bought)
    {
        $this->bought = $bought;
    }

    /**
     * @return bool
     */
    public function isRefused()
    {
        return $this->refused;
    }

    /**
     * @param bool $refused
     */
    public function setRefused($refused)
    {
        $this->refused = $refused;
    }

    /**
     * @return mixed
     */
    public function getisInPromotion()
    {
        return $this->isInPromotion;
    }

    /**
     * @param mixed $isInPromotion
     */
    public function setIsInPromotion($isInPromotion)
    {
        $this->isInPromotion = $isInPromotion;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

}

