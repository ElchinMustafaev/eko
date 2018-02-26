<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AllInfo
 *
 * @ORM\Table(name="all_info")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\AllInfoRepository")
 */
class AllInfo
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
     * @var string;
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var integer;
     *
     * @ORM\Column(name="cost", type="integer")
     */
    private $cost;

    /**
     * @var float
     *
     * @ORM\Column(name="percent", type="float")
     */
    private $percent;

    /**
     * @var integer
     *
     * @ORM\Column(name="time", type="integer")
     */
    private $time;

    /**
     * @var int
     *
     * @ORM\Column(name="ops_id", type="integer", nullable=true)
     */
    private $opsId;

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
     * @return AllInfo
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
     * @param integer $cost
     *
     * @return AllInfo
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set percent
     *
     * @param float $percent
     *
     * @return AllInfo
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return AllInfo
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set opsId
     *
     * @param integer $opsId
     *
     * @return AllInfo
     */
    public function setOpsId($opsId)
    {
        $this->opsId = $opsId;

        return $this;
    }

    /**
     * Get opsId
     *
     * @return integer
     */
    public function getOpsId()
    {
        return $this->opsId;
    }
}
