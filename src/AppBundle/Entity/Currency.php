<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Currency
 *
 * @ORM\Table(name="currencies")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CurrencyRepository")
 */
class Currency
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="exchangeRateEUR", type="float")
     */
    private $exchangeRateEUR;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="currency")
     */
    private $products;


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
     * Set name
     *
     * @param string $name
     *
     * @return Currency
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set exchangeRateEUR
     *
     * @param float $exchangeRateEUR
     *
     * @return Currency
     */
    public function setExchangeRateEUR($exchangeRateEUR)
    {
        $this->exchangeRateEUR = $exchangeRateEUR;

        return $this;
    }

    /**
     * Get exchangeRateEUR
     *
     * @return float
     */
    public function getExchangeRateEUR()
    {
        return $this->exchangeRateEUR;
    }

    public function __toString()
    {
        return $this->getName();
    }
}

