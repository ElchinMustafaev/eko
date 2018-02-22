<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Csm
 *
 * @ORM\Table(name="csm")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\CsmRepository")
 */
class Csm
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
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="cost", type="float")
     */
    private $cost;

    /**
     * @var int
     *
     * @ORM\Column(name="query", type="integer")
     */
    private $query;

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
     * @return Csm
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
     * Set cost
     *
     * @param float $cost
     *
     * @return Csm
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set query
     *
     * @param \query $query
     *
     * @return Csm
     */
    public function setQuery(\query $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return \query
     */
    public function getQuery()
    {
        return $this->query;
    }
}
