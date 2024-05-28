<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/18/15
 * Time: 10:10 AM
 */
namespace LB\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Subscriber
 * @package LB\PaymentBundle\Entity
 * @ORM\Table(name="stripe_subscription")
 * @ORM\Entity(repositoryClass="LB\PaymentBundle\Entity\Repository\SubscriberRepository")
 * @UniqueEntity(
 *     fields={"stripeId"},
 *     errorPath="stripeId",
 *     message="This id for stripe is already in use on that host."
 * )
 *  @ORM\HasLifecycleCallbacks()
 */
class Subscriber
{
    const DAY = 'day';
    const WEEK = 'week';
    const MONTH = 'month';
    const YEAR = 'year';

    const UNLIMITED = 'luvbyrd_unlimited';
    const LIKE = 'luvbyrd_like';
    const VISITOR = 'luvbyrd_visitor';
    const FAVORITE = 'luvbyrd_favorite';
    const MESSAGE = 'luvbyrd_message';


    static $PLAN = array(
        self::UNLIMITED => 'Unlimited package',
        self::LIKE => 'Likes',
        self::VISITOR => 'Visitors',
        self::FAVORITE => 'Favorites',
        self::MESSAGE => 'Unlimited Messaging'
    );

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *  @Groups({"plan"})
     */
    protected $id;

    /**
     * @ORM\Column(name="strip_id", type="string", unique=true)
     */
    protected $stripeId;

    /**
     * @var
     * @ORM\Column(name="stripe_plan", type="string")
     */
    private $stripePlan;

    /**
     * @var
     * @ORM\Column(name="hide", type="boolean")
     */
    private $hide = false;

    /**
     * @var
     * @ORM\Column(name="description", type="string", nullable=true )
     * @Groups({"plan"})
     */
    private $description;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $amount;

    /**
     * @var string
     */
    public $currency = 'USD';

    /**
     * @var
     */
    public $interval;

    /**
     * @var
     */
    public $intervalCount;

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    function __toString()
    {
        // get stripe plan
        $stripePlan = $this->getStripePlan();

        return (isset($stripePlan) && isset($stripePlan['name'])) ? $stripePlan['name'] : ($this->getStripeId() ? $this->getStripeId(): '');
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStripePlan()
    {
        return json_decode($this->stripePlan, true);
    }

    /**
     * @return mixed
     * @VirtualProperty()
     */
    public function getPlanInfo()
    {
        return json_decode($this->stripePlan, true);
    }

    /**
     * @param array $plan
     */
    public function setStripePlan(array $plan)
    {
        $this->stripePlan = json_encode($plan);
    }

    /**
     * @return mixed
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * @param mixed $stripeId
     */
    public function setStripeId($stripeId)
    {
        $this->stripeId = $stripeId;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getHide()
    {
        return $this->hide;
    }

    /**
     * @param mixed $hide
     */
    public function setHide($hide)
    {
        $this->hide = $hide;
    }

    /**
     * @ORM\PostLoad()
     */
    public function generateSubscriber()
    {
        $stripePlan = $this->getStripePlan();
        if($stripePlan){
            $this->name = isset($stripePlan['name']) ? $stripePlan['name'] : null;
            $this->interval = isset($stripePlan['interval']) ? $stripePlan['interval'] : null;
            $this->intervalCount = isset($stripePlan['interval_count']) ? $stripePlan['interval_count'] : null;
            $this->amount = isset($stripePlan['amount']) ? $stripePlan['amount'] : null;
            $this->currency = isset($stripePlan['currency']) ? $stripePlan['currency'] : null;
        }
    }

}
