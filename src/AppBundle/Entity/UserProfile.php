<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * @ORM\Table(name="user_profile")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserProfileRepository")
 */
class UserProfile
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
     * @var float
     *
     * @ORM\Column(name="cash", type="float", nullable=true)
     */
    private $cash;

    /**
     * @ORM\Column(name="currency", type="string", length=255, nullable=true)
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Currency", inversedBy="userProfiles")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @var int
     *
     * @ORM\Column(name="purchaseCount", type="integer", nullable=true)
     */
    private $purchaseCount;

    /**
     * @var float
     *
     * @ORM\Column(name="purchasesValue", type="float", nullable=true)
     */
    private $purchasesValue;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSeller", type="boolean", nullable=true)
     */
    private $isSeller;

    /**
     * @var int
     *
     * @ORM\Column(name="salesCount", type="integer", nullable=true)
     */
    private $salesCount;

    /**
     * @var float
     *
     * @ORM\Column(name="salesValue", type="float", nullable=true)
     */
    private $salesValue;

    /**
     * @var float
     *
     * @ORM\Column(name="rating", type="float", nullable=true)
     */
    private $rating;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", mappedBy="userProfile", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

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
     * Set cash
     *
     * @param float $cash
     *
     * @return UserProfile
     */
    public function setCash($cash)
    {
        $this->cash = $cash;

        return $this;
    }

    /**
     * Get cash
     *
     * @return float
     */
    public function getCash()
    {
        return $this->cash;
    }

    /**
     * Set purchaseCount
     *
     * @param integer $purchaseCount
     *
     * @return UserProfile
     */
    public function setPurchaseCount($purchaseCount)
    {
        $this->purchaseCount = $purchaseCount;

        return $this;
    }

    /**
     * Get purchaseCount
     *
     * @return int
     */
    public function getPurchaseCount()
    {
        return $this->purchaseCount;
    }

    /**
     * Set purchasesValue
     *
     * @param float $purchasesValue
     *
     * @return UserProfile
     */
    public function setPurchasesValue($purchasesValue)
    {
        $this->purchasesValue = $purchasesValue;

        return $this;
    }

    /**
     * Get purchasesValue
     *
     * @return float
     */
    public function getPurchasesValue()
    {
        return $this->purchasesValue;
    }

    /**
     * Set isSeller
     *
     * @param boolean $isSeller
     *
     * @return UserProfile
     */
    public function setIsSeller($isSeller)
    {
        $this->isSeller = $isSeller;

        return $this;
    }

    /**
     * Get isSeller
     *
     * @return bool
     */
    public function getIsSeller()
    {
        return $this->isSeller;
    }

    /**
     * Set salesCount
     *
     * @param integer $salesCount
     *
     * @return UserProfile
     */
    public function setSalesCount($salesCount)
    {
        $this->salesCount = $salesCount;

        return $this;
    }

    /**
     * Get salesCount
     *
     * @return int
     */
    public function getSalesCount()
    {
        return $this->salesCount;
    }

    /**
     * Set salesValue
     *
     * @param float $salesValue
     *
     * @return UserProfile
     */
    public function setSalesValue($salesValue)
    {
        $this->salesValue = $salesValue;

        return $this;
    }

    /**
     * Get salesValue
     *
     * @return float
     */
    public function getSalesValue()
    {
        return $this->salesValue;
    }

    /**
     * Set rating
     *
     * @param float $rating
     *
     * @return UserProfile
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
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



    public function __toString()
    {
        return ' ';
    }
}

