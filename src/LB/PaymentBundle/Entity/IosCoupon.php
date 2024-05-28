<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 5/23/16
 * Time: 4:33 PM
 */

namespace LB\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="ios_coupon")
 * @ORM\Entity(repositoryClass="LB\PaymentBundle\Entity\Repository\CouponRepository")
 */
class IosCoupon
{
    const DEPRECATED = true;
    const ACTIVE = false;
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="couponId", type="string", unique=true)
     */
    protected $couponId;

    /**
     * @ORM\Column(name="durationInDay", type="integer", nullable=false)
     */
    protected $durationInDay;

    /**
     * @ORM\Column(name="deprecated", type="boolean", nullable=true)
     */
    protected $deprecated = self::ACTIVE;

    /**
     * @ORM\Column(name="userIds", type="array", nullable=true)
     */
    protected $userIds;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set couponId
     *
     * @param string $couponId
     *
     * @return Coupon
     */
    public function setCouponId($couponId)
    {
        $this->couponId = $couponId;

        return $this;
    }

    /**
     * Get couponId
     *
     * @return string
     */
    public function getCouponId()
    {
        return $this->couponId;
    }

    /**
     * @param $userId
     * @return $this
     */
    public function addUserId($userId)
    {
        $this->userIds[] = $userId;

        return $this;
    }

    /**
     * @param $userId
     */
    public function removeUserId($userId)
    {
        $this->userIds->removeElement($userId);
    }

    /**
     * @return mixed
     */
    public function getUserIds()
    {
        return $this->userIds;
    }

    /**
     * @param $deprecated
     * @return $this
     */
    public function setDeprecated($deprecated)
    {
        $this->deprecated = $deprecated;

        return $this;
    }

    /**
     * Get duration
     *
     * @return string
     */
    public function getDeprecated()
    {
        return $this->deprecated;
    }

    /**
     * Set durationInMonth
     *
     * @param integer $durationInDay
     *
     * @return Coupon
     */
    public function setDurationInDay($durationInDay)
    {
        $this->durationInDay = $durationInDay;

        return $this;
    }

    /**
     * Get durationInDay
     *
     * @return integer
     */
    public function getDurationInDay()
    {
        return $this->durationInDay;
    }
    
}
