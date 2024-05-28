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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Subscriber
 * @package LB\PaymentBundle\Entity
 * @ORM\Table(name="stripe_customer")
 * @ORM\Entity()
 */
class Customer
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="strip_id", type="string", unique=true)
     */
    protected $stripeCustomerId;

    /**
     * @var
     * @ORM\Column(name="stripe_customer", type="text")
     */
    private $stripeCustomer;

    /**
     * @var
     * @ORM\Column(name="stripe_plan", type="text")
     */
    private $stripePlan;

    /**
     * @ORM\OneToOne(targetEntity="LB\UserBundle\Entity\User", inversedBy="customer")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
     * Set stripeCustomerId
     *
     * @param string $stripeCustomerId
     *
     * @return Customer
     */
    public function setStripeCustomerId($stripeCustomerId)
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }

    /**
     * Get stripeCustomerId
     *
     * @return string
     */
    public function getStripeCustomerId()
    {
        return $this->stripeCustomerId;
    }

    /**
     * Set stripeCustomer
     *
     * @param string $stripeCustomer
     *
     * @return Customer
     */
    public function setStripeCustomer($stripeCustomer)
    {
        // check customer id
        if(is_array($stripeCustomer) && array_key_exists('id', $stripeCustomer)){
            $this->setStripeCustomerId($stripeCustomer['id']);

        }

        $this->stripeCustomer = json_encode($stripeCustomer);

        return $this;
    }

    /**
     * Get stripeCustomer
     *
     * @return string
     */
    public function getStripeCustomer()
    {
        return  json_decode($this->stripeCustomer, true);
    }

    /**
     * Set user
     *
     * @param \LB\UserBundle\Entity\User $user
     *
     * @return Customer
     */
    public function setUser(\LB\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @return $this
     */
    public function resetStripePlan()
    {
        $this->stripePlan = json_encode([]);

        return $this;
    }


    /**
     * @param $planId
     * @param $plan
     * @return $this
     */
    public function addStripePlan($planId, $plan)
    {
        // get stripe Plans
        $plans = $this->getStripePlan();

        // check customer id
        if(is_array($plans)){

            if(!array_key_exists($planId, $plans)){
                $plans[$planId] = $plan;
            }
        }
        else{
            $plans = array($planId => $plan);
        }

        $this->stripePlan = json_encode($plans);

        return $this;
    }


    /**
     * @param $planId
     * @return $this
     */
    public function deleteStripePlan($planId)
    {
        // get stripe Plans
        $plans = $this->getStripePlan();

        // check customer id
        if(is_array($plans) && array_key_exists($planId, $plans)){
            unset($plans[$planId]);
        }

        $this->stripePlan = json_encode($plans);

        return $this;
    }

    /**
     * Get stripeCustomer
     *
     * @return string
     */
    public function getStripePlan()
    {
        return  json_decode($this->stripePlan, true);
    }
}
