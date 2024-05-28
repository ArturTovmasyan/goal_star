<?php

namespace LB\PaymentBundle\Services;

use AppBundle\Entity\ConnectionInfo;
use Doctrine\ORM\EntityManager;
use LB\PaymentBundle\Entity\Subscriber;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Customer;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class Stripe
 * @package LB\PaymentBundle\Services
 */
class Stripe
{

    /**
     * @var
     */
    private $container;

    /**
     * @var
     */
    private $publishKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $em;

    /**
     * @param $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $live = $container->getParameter('stripe_live');
        $this->publishKey = $live ? $container->getParameter('stripe_publish_key_live') : $container->getParameter('stripe_publish_key_sandbox');
        $this->secretKey = $live ? $container->getParameter('stripe_sekret_key_live') : $container->getParameter('stripe_sekret_key_sandbox');;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @param $customerId
     * @param $token
     */
    public function changeCard($customerId, $token)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $cu = \Stripe\Customer::retrieve($customerId);
        $cu->source = $token; // obtained with Stripe.js
        $cu->save();
    }

    /**
     * @return array|mixed
     */
    public function getAllCustomer()
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $customers = \Stripe\Customer::all();

        return $customers->jsonSerialize();
    }


    /**
     * @param array $params
     * @return \Stripe\Plan
     */
    public function createCoupon(array $params)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $cpn = \Stripe\Coupon::create($params);

        return $cpn->jsonSerialize();
    }

    /**
     * @param $couponId
     * @return array|mixed
     */
    public function getCoupon($couponId)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        try{
            $cpn = \Stripe\Coupon::retrieve($couponId);
            return $cpn->jsonSerialize();
        }
        catch(\Exception $e){
            return null;
        }
    }

    /**
     * @param $customerId
     * @return array|mixed|null
     */
    public function getCustomer($customerId)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        try{
            $customer =  \Stripe\Customer::retrieve($customerId);
            return $customer->jsonSerialize();
        }
        catch(\Exception $e){
            return null;
        }
    }

    /**
     * @param $couponId
     */
    public function deleteCoupon($couponId)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $cpn = \Stripe\Coupon::retrieve($couponId);
        $cpn->delete();
    }

    /**
     * @param array $params
     * @return \Stripe\Plan
     */
    public function createPlan(array $params)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $p = \Stripe\Plan::create($params);
        return $p;
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function getPlan($id)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        try{
            $plan =  \Stripe\Plan::retrieve($id);
            $plan =  $plan->jsonSerialize();
        }
        catch(\Exception $e){
            $plan = null;
        }

        return $plan;
    }

    /**
     * @return array
     */
    private function stripeData()
    {
        $stripeData = array(
//            'luvbyrd_message' => array(
//                'amount' => 500,
//                'currency' => 'usd',
//                'interval'=> 'month',
//                'name' =>'Unlimited Messaging',
//                'description' => 'Message who you want, when you want.'
//                ),
            'luvbyrd_favorite' => array(
                'amount' => 300,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Favorites',
                'description'=> 'Know who’s favorited you as their favorite LuvByrd.'
            ),
            'luvbyrd_visitor' => array(
                'amount' => 300,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Visitors',
                'description' => 'We know you want to know who’s checking you out!'
            ),
            'luvbyrd_like' => array(
                'amount' => 300,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Likes',
                'description' => 'See who’s liked you. Who knows, they might be the one :)'
            ),
            'luvbyrd_unlimited' => array(
                'amount' => 1000,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Unlimited package',
                'description' => 'Includes unlimited messaging, access to likes, visitors, favorites, unlimited messaging and connections.
'
            ),
        );
        return $stripeData;
    }


    /**
     * @return array
     */
    private function newStripeData()
    {
        $stripeData = array(
            'luvbyrd_favorite_new' => array(
                'amount' => 500,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Favorites',
                'description'=> 'Know who’s favorited you as their favorite LuvByrd.'
            ),
            'luvbyrd_visitor_new' => array(
                'amount' => 500,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Visitors',
                'description' => 'We know you want to know who’s checking you out!'
            ),
            'luvbyrd_like_new' => array(
                'amount' => 500,
                'currency' => 'usd',
                'interval'=> 'month',
                'name' =>'Likes',
                'description' => 'See who’s liked you. Who knows, they might be the one :)'
            ),
        );
        return $stripeData;
    }

    public function addNewPlans()
    {
        $em = $this->em;

        // gew new strip data
        $localStripeData = $this->newStripeData();

        // loop for stripe data
        foreach($localStripeData as $subscribeId => $stripeData){

            // get old subscribe id
            $oldSubscribeId = str_replace('_new', '',$subscribeId);

            // get subscribe from database
            $oldSubscribe = $em->getRepository('LBPaymentBundle:Subscriber')->findOneBy(array('stripeId' => $oldSubscribeId));

            // check subscribe
            if($oldSubscribe){

                // set hide true
                $oldSubscribe->setHide(true);
                $em->persist($oldSubscribe);
            }

            $subscribe = new Subscriber();
            $subscribe->setStripeId($subscribeId);
            $subscribe->setDescription($stripeData['description']);

            // create stripe plan
            $stripe = $this->createPlan(array(
                "id" => $subscribeId,
                "amount" => $stripeData['amount'],
                "currency" => $stripeData['currency'],
                "interval" => $stripeData['interval'],
                "name" => $stripeData['name'],

            ));
            // serialize strip data
            $stripePlan = $stripe->jsonSerialize();


            // set params
            $subscribe->setStripePlan($stripePlan);

            $em->persist($subscribe);

        }

        $em->flush();
    }

    /**
     * @param array $params
     * @return \Stripe\Plan
     */
    public function synchronizePlans()
    {

        // get all subscribers
        $subscribers = $this->em->getRepository('LBPaymentBundle:Subscriber')->findAllSubscribers();

        $localStripeData = $this->stripeData();

        // loop for stripe data
        foreach($localStripeData as $subscribeId => $stripeData){

            if(array_key_exists($subscribeId, $subscribers)){
                $subscribe = $subscribers[$subscribeId];
            }
            else{
                $subscribe = new Subscriber();
            }

            $subscribe->setStripeId($subscribeId);
            $subscribe->setDescription($stripeData['description']);

            $stripePlan = $this->getPlan($subscribeId);

            if(!$stripePlan){
                // create stripe plan
                $stripe = $this->createPlan(array(
                    "id" => $subscribeId,
                    "amount" => $stripeData['amount'],
                    "currency" => $stripeData['currency'],
                    "interval" => $stripeData['interval'],
                    "name" => $stripeData['name'],

                ));
                // serialize strip data
                $stripePlan = $stripe->jsonSerialize();

            }

            // set params
            $subscribe->setStripePlan($stripePlan);

            $this->em->persist($subscribe);
        }

        $this->em->flush();
    }

    /**
     * @param $stripeId
     * @return string
     */
    private function generateDescription($stripeId)
    {
        $trans = $this->container->get('translator');

        switch($stripeId){
            case Subscriber::LIKE:
                $description = $trans->trans('subscriber.like');
                break;
            case Subscriber::FAVORITE:
                $description = $trans->trans('subscriber.favorite');
                break;
            case Subscriber::VISITOR:
                $description = $trans->trans('subscriber.like');
                break;
            case Subscriber::MESSAGE:
                $description = $trans->trans('subscriber.message');
                break;
            case Subscriber::UNLIMITED:
                $description = $trans->trans('subscriber.unlimited');
                break;
            default:
                $description = '';
                break;
        }

        return $description;

    }


    /**
     * @param $planId
     * @return \Stripe\Plan
     */
    public function updatePlan($planId, $name)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $p = \Stripe\Plan::retrieve($planId);
        $p->name = $name;
        $p->save();
        return $p;
    }


    /**
     * @param $customerId
     * @return array|mixed
     */
    public function customerPayments($customerId)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $list = \Stripe\Charge::all(array("customer" => $customerId));
        return $list->jsonSerialize();
    }


    /**
     * @param $amount
     * @param $currency
     * @param $token
     */
    public function buyTicket($amount, $currency, $token)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $charge = \Stripe\Charge::create(array(
            "amount"      => $amount,
            "currency"    => $currency,
            "description" => "",
            "source"      => $token,
        ));

        return $charge->getLastResponse()->code;
    }

    /**
     * @param $customerId
     * @param $token
     * @return array|mixed
     */
    public function updateCustomer($customerId, $token)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $cu = \Stripe\Customer::retrieve($customerId);
        $cu->source = $token; // obtained with Stripe.js
        $cu->save();
        return $cu->jsonSerialize();
    }

    /**
     * @param $chargeId
     * @param $amount
     * @return \Stripe\Refund
     */
    public function refundCharge($chargeId, $amount)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $re = \Stripe\Refund::create(array(
            "charge" => $chargeId,
            "amount" => $amount,
        ));


        return $re;
    }


    /**
     * @param $planId
     * @return \Stripe\Plan
     */
    public function deletePlan($planId)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);
        $plan = \Stripe\Plan::retrieve($planId);
        return $plan->delete();
    }

    /**
     * @param $token
     * @param $email
     * @param $subscription
     * @return Customer
     */
    public function createCustomer($token, $email, $subscription, $coupon = null)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);


        $params = (array(
            "source" => $token,
            "plan" => $subscription->getStripeId(),
            "email" => $email)
        );

        if(strlen($coupon) > 1){
            $params["coupon"] = $coupon;
        }
        $customer = \Stripe\Customer::create($params);

        return $customer->jsonSerialize();
    }

    /**
     * @param $customer
     * @param $subscription
     * @return array|mixed
     * @param null $coupon
     */
    public function createSubscription($customer, $subscription, $coupon = null)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $params = array(
            "customer" => $customer->getStripeCustomerId(),
            "plan" => $subscription->getStripeId()
        );

        if(strlen($coupon) > 1){
            $params["coupon"] = $coupon;
        }

        $customer = \Stripe\Subscription::create($params );

        return $customer->jsonSerialize();
    }

    /**
     * @param $subscriptionId
     * @return array|mixed
     */
    public function cancelSubscription($subscriptionId)
    {

        \Stripe\Stripe::setApiKey($this->secretKey);

        $sub = \Stripe\Subscription::retrieve($subscriptionId);
        $p = $sub->cancel();
        return $p->jsonSerialize();
    }


    /**
     * @param $subscriptionId
     * @param $newPlan
     * @param $coupon
     * @return array|mixed
     */
    public function updateSubscription($subscriptionId, $newPlan, $coupon = null)
    {

        \Stripe\Stripe::setApiKey($this->secretKey);

        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        $subscription->plan = $newPlan;
        if(strlen($coupon) > 1){
            $subscription->coupon = $coupon;
        }

        $plan = $subscription->save();

        return $plan->jsonSerialize();
    }

    /**
     * @param int $count
     * @param $customer
     * @return array|mixed
     */
    public function listInvoice($customer, $count = null)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $invoices = \Stripe\Invoice::all(array("limit" => $count, "customer" => $customer));
        return $invoices->jsonSerialize();
    }

    /**
     * @param int $count
     * @param $customer
     * @return array|mixed
     */
    public function listSubscription($customer, $count = null)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $invoices = \Stripe\Subscription::all(array("limit" => $count, "customer" => $customer, 'status' => 'active'));
        return $invoices->jsonSerialize();
    }

    /**
     * @param $customer
     * @param $startDate
     * @param $subscribeId
     * @param null $count
     * @return mixed
     */
    public function getInvoiceBySubscribe($customer, $startDate, $subscribeId, $count = null)
    {
        \Stripe\Stripe::setApiKey($this->secretKey);

        $invoices = \Stripe\Invoice::all(array("limit" => $count,  "customer" => $customer,  'date[gt]'=> $startDate));

        $invoices = $invoices->jsonSerialize();

        $objects = $invoices['data'];

        if(count($objects) > 0){

            foreach ($objects as $object){
                if($object['subscription'] == $subscribeId && $object['paid'] == true){
                    return array('charge' => $object['charge'], 'total' => $object['total']);
                }
            }
        }
        return null;
    }
}