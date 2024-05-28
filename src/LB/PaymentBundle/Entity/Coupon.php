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
 * @ORM\Table(name="stripe_coupon")
 * @ORM\Entity(repositoryClass="LB\PaymentBundle\Entity\Repository\CouponRepository")
 */
class Coupon
{
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
     * @ORM\Column(name="percent_off", type="integer", nullable=true)
     */
    protected $percentOff;

    /**
     * @ORM\Column(name="amount_off", type="float", nullable=true)
     */
    protected $amountOff;

    /**
     * @ORM\Column(name="duration", type="string", length=10)
     */
    protected $duration;

    /**
     * @ORM\Column(name="durationInMonth", type="integer", nullable=true)
     */
    protected $durationInMonth;

    /**
     * @ORM\Column(name="max_redemption", type="integer", nullable=true)
     */
    protected $maxRedemption;

    /**
     * @var
     * @ORM\Column(name="redeem_by", type="datetime", nullable=true)
     */
    protected $redeemBy;

    /**
     * @var string
     */
    public $currency = 'usd';

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
     * Set percentOff
     *
     * @param integer $percentOff
     *
     * @return Coupon
     */
    public function setPercentOff($percentOff)
    {
        $this->percentOff = $percentOff;

        return $this;
    }

    /**
     * Get percentOff
     *
     * @return integer
     */
    public function getPercentOff()
    {
        return $this->percentOff;
    }

    /**
     * Set amountOff
     *
     * @param float $amountOff
     *
     * @return Coupon
     */
    public function setAmountOff($amountOff)
    {
        $this->amountOff = $amountOff;

        return $this;
    }

    /**
     * Get amountOff
     *
     * @return float
     */
    public function getAmountOff()
    {
        return $this->amountOff;
    }

    /**
     * Set duration
     *
     * @param string $duration
     *
     * @return Coupon
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set durationInMonth
     *
     * @param integer $durationInMonth
     *
     * @return Coupon
     */
    public function setDurationInMonth($durationInMonth)
    {
        $this->durationInMonth = $durationInMonth;

        return $this;
    }

    /**
     * Get durationInMonth
     *
     * @return integer
     */
    public function getDurationInMonth()
    {
        return $this->durationInMonth;
    }

    /**
     * Set maxRedemption
     *
     * @param integer $maxRedemption
     *
     * @return Coupon
     */
    public function setMaxRedemption($maxRedemption)
    {
        $this->maxRedemption = $maxRedemption;

        return $this;
    }

    /**
     * Get maxRedemption
     *
     * @return integer
     */
    public function getMaxRedemption()
    {
        return $this->maxRedemption;
    }

    /**
     * Set redeemBy
     *
     * @param \DateTime $redeemBy
     *
     * @return Coupon
     */
    public function setRedeemBy($redeemBy)
    {
        $this->redeemBy = $redeemBy;

        return $this;
    }

    /**
     * Get redeemBy
     *
     * @return \DateTime
     */
    public function getRedeemBy()
    {
        return $this->redeemBy;
    }

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        // check if null percent off and amount off
        if(!$this->amountOff && !$this->percentOff){

            $context->buildViolation('One of this values is required')
                ->atPath('percentOff')
                ->addViolation();
            $context->buildViolation('One of this values is required')
                ->atPath('amountOff')
                ->addViolation();
        }

        // check if null percent off and amount off
        if($this->amountOff && $this->percentOff){

            $context->buildViolation('Only one of this values must be fill in')
                ->atPath('percentOff')
                ->addViolation();
            $context->buildViolation('Only one of this values must be fill in')
                ->atPath('amountOff')
                ->addViolation();
        }

        // check is repeating and duration in month is null
        if($this->duration == 'repeating' && !$this->durationInMonth){
            $context->buildViolation('Duration In MonthOnly is required if duration is repeating')
                ->atPath('durationInMonth')
                ->addViolation();
        }

        if($this->redeemBy && $this->redeemBy <= new \DateTime()){
            $context->buildViolation('RedeemBy must be date in future')
                ->atPath('redeemBy')
                ->addViolation();
        }

    }
}
