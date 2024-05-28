<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 6/16/16
 * Time: 12:52 PM
 */
namespace LB\PaymentBundle\Controller\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use LB\PaymentBundle\Entity\Coupon;
use LB\PaymentBundle\Entity\Customer;
use LB\PaymentBundle\Entity\Subscriber;
use LB\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @Rest\RouteResource("Payment")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class PaymentController extends FOSRestController
{

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         404="Bad request",
     *         204="There is no information to send back"
     *     },
     * parameters={
     *      {"name"="planId", "dataType"="string", "required"=true, "description"="Stripe plan id"},
     *      {"name"="days", "dataType"="integer", "required"=true, "description"="Subscribe days"},
     * }
     * )
     * @param Request $request
     * @return JsonResponse
     * @Rest\View(serializerGroups={"user_for_mobile", "user_for_mobile_status", "for_mobile"})
     * @param Request $request
     */
    public function postPurchaseAction(Request $request)
    {
        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $plans = $em->getRepository('LBPaymentBundle:Subscriber')->findSubscriberStripId();

        // get subscriber type
        $token = $request->headers->get('token');  // get token

        // generate my tocken
        $myToken = md5($currentUser->getId() .'_' .$currentUser->getFirstName());

        if($token != $myToken){
            // return
            return new JsonResponse(array('error'=> 'token not found'), Response::HTTP_BAD_REQUEST);
        }


        $planId = $request->get('planId');  // get token
        $days = $request->get('days');  // get token

        // check plan id
        if(!in_array($planId, $plans)){
            return new JsonResponse(array('error'=> 'plan id not found'), Response::HTTP_BAD_REQUEST);
        }

        // check days
        if(!$days && $days < 1){
            return new JsonResponse(array('error'=> 'incorrect day'), Response::HTTP_BAD_REQUEST);
        }

        // get trial period
        $trialPeriod = $currentUser->getTrialPeriod();

        $today = new \DateTime();

        // check trial period
        if(is_array($trialPeriod) && array_key_exists($planId, $trialPeriod)){
            $oldPeriod = $trialPeriod[$planId];
            $oldPeriod = new \DateTime("@$oldPeriod");

            if($oldPeriod <= $today){
                $period = $today->modify("+$days days");
            }else{
                $period = $oldPeriod->modify("+$days days");
            }
        }
        else{
            $period = $today->modify("+$days days");
        }

        $period = $period->getTimestamp();

        $currentUser->setTrialPeriod($planId, $period);

        $em->persist($currentUser);
        $em->flush();

        return $currentUser;

    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         404="Bad request",
     *         204="There is no information to send back"
     *     },
     * parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Stripe token"}
     * }
     * )
     * @param Request $request
     * @return JsonResponse
     * @Rest\View()
     * @param Request $request
     */
    public function postCardAction(Request $request)
    {
        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get subscriber type
        $token = $request->headers->get('token') ? $request->headers->get('token'): $request->get('token');  // get token

        if(!$token){
            return new JsonResponse(array('error'=> 'Token not found'), Response::HTTP_BAD_REQUEST);
        }

        // get stripe
        $stripe = $this->container->get('lb.stripe');

        // get customer
        $customer = $currentUser->getCustomer();

        //check if not logged in user
        if(!is_object($customer)) {
            return new JsonResponse(array('error'=> 'Customer not found'), Response::HTTP_BAD_REQUEST);
        }

        try{
            $stripe->changeCard($customer->getStripeCustomerId(), $token);
            return new JsonResponse(null, Response::HTTP_OK);


        }catch (\Exception $e){
            return new JsonResponse(array('error'=> $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         404="Not found"
     *     },
     *  parameters={
     *      {"name"="coupon", "dataType"="string", "required"=true, "description"="Coupon id"},
     *      {"name"="price", "dataType"="float", "required"=true, "description"="Price id"},
     * }
     * )
     *
     * @Rest\View()
     * @param Request $request
     * @return float|JsonResponse
     */
    public function getDiscountPriceAction(Request $request)
    {

        $couponId = $request->headers->get('coupon') ? $request->headers->get('coupon') : $request->get('coupon'); // get coupon id

        if(!$couponId){
            return new JsonResponse('Missing coupon', Response::HTTP_BAD_REQUEST);
        }

        $price = $request->headers->get('price') ? $request->headers->get('price'): $request->get('price');  // get token

        if(!$price){
            return new JsonResponse('Missing price', Response::HTTP_BAD_REQUEST);
        }

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get subscribers
        $coupon = $em->getRepository('LBPaymentBundle:Coupon')->findOneBy(array('couponId' => $couponId));

        // check coupon
        if(!$coupon){
            return new JsonResponse('coupon not found', Response::HTTP_NOT_FOUND);
        }

        // get percent off
        $percentOff = $coupon->getPercentOff();
        $amountOff = $coupon->getAmountOff();

        $discount = 0;

        // check percent off
        if($percentOff){

            $discount = $price * $percentOff / 100;

        }
        elseif($amountOff){
            $discount = $price * $percentOff / 100;
            $discount = $discount < 0 ? 0 : $discount;
        }

        return ($price - $discount);
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"plan"})
     */
    public function cgetAction(Request $request )
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $stripePublishKey = $this->container->getParameter('stripe_live') ? $this->container->getParameter('stripe_publish_key_live') : $this->container->getParameter('stripe_publish_key_sandbox');

        // get subscribers 
        $subscribers = $em->getRepository('LBPaymentBundle:Subscriber')->findAllSubscribers();

        return array('stripePublishKey' => $stripePublishKey, 'subscribers' => array_values($subscribers));
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function getSearchAction(Request $request )
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get search data
        $search = $request->get('q');

        // get subscribers
        $subscribers = $em->getRepository('LBPaymentBundle:Subscriber')->findAllForSelect2($search);

        return array("results" => $subscribers);
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function getCouponAction($couponId)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }
        
        if(!$couponId){
            return new JsonResponse('coupon id is null', Response::HTTP_BAD_REQUEST);
        }

        $coupon = $em->getRepository('LBPaymentBundle:IosCoupon')->findOneBy(array('couponId' => $couponId));

        if(!$coupon){
            return new JsonResponse('Coupon not found', Response::HTTP_BAD_REQUEST);
        } elseif ($coupon->getDeprecated()){
            return new JsonResponse('Coupon is not valid ', Response::HTTP_BAD_REQUEST);
        } elseif ($coupon->getUserIds() && in_array($currentUser->getId(),$coupon->getUserIds())){
            return new JsonResponse('Coupon is already use ', Response::HTTP_BAD_REQUEST);
        }

        return array("coupon" => $coupon);
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function useCouponAction($couponId)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        if(!$couponId){
            return new JsonResponse('coupon id is null', Response::HTTP_BAD_REQUEST);
        }

        $coupon = $em->getRepository('LBPaymentBundle:IosCoupon')->findOneBy(array('couponId' => $couponId));

        if(!$coupon){
            return new JsonResponse('coupon not found', Response::HTTP_BAD_REQUEST);
        } elseif ($coupon->getDeprecated()){
            return new JsonResponse('coupon is not valid ', Response::HTTP_BAD_REQUEST);
        }elseif ($coupon->getUserIds() && in_array($currentUser->getId(),$coupon->getUserIds())){
            return new JsonResponse('Coupon is already use ', Response::HTTP_BAD_REQUEST);
        }

        $coupon->addUserId($currentUser->getId());
        $em->flush();

        return array("coupon" => $coupon);
    }

    /**
     * @param $endPeriod
     * @param $totalAmount
     * @return float|int
     */
    private function generateAmount($endPeriod, $totalAmount)
    {
        $totalDays = 30;
        $currentDate = new \DateTime();

        $interval = date_diff($endPeriod, $currentDate);
        $day = $interval->days;

        if($day == 0){
            $amount = 0;
        }
        else{
            $amount = ($totalAmount * $totalDays) / $day;
        }
        $amount = $amount < 0 ? 0  : $amount;
        $amount = $totalAmount ? $totalAmount : $amount;

        return $amount;
    }

    /**
     * @param $timestamp
     * @return bool
     */
    private function notExpired($timestamp)
    {
        $day = new \DateTime();
        $time = $day->getTimestamp();

        if($time < $timestamp){
            return true;
        }

        return false;
    }

    /**
     * @param $customer
     * @param $stripePlans
     */
    private function synchronizePlans(&$customer, $stripePlans)
    {
        $dbPlans = $customer->getStripePlan();
        if(is_array($dbPlans)){
            foreach ($dbPlans as $planId => $plan){
                $newPlanId = strpos($planId, '_new') === false ? $planId . '_new' : $planId;
                // check for old plan
                $oldPlanId = str_replace('_new', '', $planId);

                if(!in_array($newPlanId, $stripePlans) && !in_array($oldPlanId, $stripePlans)){
                    $customer->deleteStripePlan($planId);
                }
            }
        }
    }


    /**
     * @param Customer $customer
     */
    private function checkDataWithStripe(Customer $customer)
    {
        // get stripe
        $stripe = $this->container->get('lb.stripe');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get customer by id
        $stripeCustomer = $stripe->getCustomer($customer->getStripeCustomerId());

        //check customer, and remove if not exist
        if($stripeCustomer){
            // check stripe subscriptions with our
            $stripSubscriptions = $stripeCustomer['subscriptions']['data'];
            $stripePlans = array();

            // subscription exist in stripe
            if(count($stripSubscriptions) > 0){

                // loop for stripe subscription
                foreach ($stripSubscriptions as $stripSubscription){
                    $stripePlans[] = $stripSubscription['plan']['id'];
                }

                $this->synchronizePlans($customer, $stripePlans);
                $em->persist($customer);


            }else{// subscription not exist in stripe, reset our subscriptions
                $customer->resetStripePlan();
                $em->persist($customer);
            }
        }else{
            $user = $customer->getUser();
            $customer->setUser(null);
            $user->setCustomer(null);

            $em->remove($customer);
            $em->persist($user);
        }

        $em->flush();
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     },
     * parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "description"="Stripe token"},
     *      {"name"="subscriberId", "dataType"="integer", "required"=true, "description"="Plan id"},
     * }
     * )
     *
     * @Rest\View(serializerGroups={"user_for_mobile", "user_for_mobile_status", "for_mobile"})
     */
    public function putAction(Request $request )
    {

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get subscriber type
        $planId = $request->get('subscriberId');
        $couponId = $request->headers->get('coupon') ? $request->headers->get('coupon') : $request->get('coupon'); // get coupon id
        $token = $request->headers->get('token') ? $request->headers->get('token'): $request->get('token');  // get token

        // get stripe
        $stripe = $this->container->get('lb.stripe');

        if(is_numeric($planId)){
            // get event
            $event = $em->getRepository('AppBundle:Event')->findOneBy(array('id' => $planId));
            
            // check have user this ticket
            if($event->getUsers()->contains($currentUser)){
                return new JsonResponse('You already buy this ticket', Response::HTTP_BAD_REQUEST);
            }

            // get Donate
            $donate = $request->get('donate');

            $amount = $event->getPrice()?$event->getPrice():$donate;
            
            $status = $stripe->buyTicket($amount * 100, 'usd', $token);
            if($status == 200){
                $event->addUser($currentUser);
                $em->flush();
            }
            
            return $status;
        }
        // check coupon
        if(strlen($couponId) > 1){

            // check stripe coupon by coupon id
            $coupon = $stripe->getCoupon($couponId);

            // return error if coupon does not exist
            if(!$coupon){
                return new JsonResponse("Coupon does not exist", Response::HTTP_BAD_REQUEST);
            }

            $metadata = $coupon['metadata'];

            if($metadata && is_array($metadata)){
                $metadata = array_keys($metadata);

                // check plan in coupons metadata
                if(!in_array($planId, $metadata)){
                    return new JsonResponse("Coupon for that plan does not exist", Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // get user subscribers
        $userSubscribers = $currentUser->getTrialPeriod();

        // check has user subscribes
        if(is_array($userSubscribers)){

            // check has user unlimited package
            if(array_key_exists(Subscriber::UNLIMITED, $userSubscribers) &&
                $this->notExpired($userSubscribers[Subscriber::UNLIMITED])){
                return new JsonResponse('You have maximal unlimited package', Response::HTTP_BAD_REQUEST);
            }

            // check have user this plan
            if(array_key_exists($planId, $userSubscribers) && $this->notExpired($userSubscribers[$planId])){
                return new JsonResponse('You already subscribed this plan', Response::HTTP_BAD_REQUEST);
            }

        }

        // get subscription from admin
        $subscription = $em->getRepository('LBPaymentBundle:Subscriber')->findOneBy(array('stripeId' => $planId));

        //check subscription
        if(!is_object($subscription)) {

            return new JsonResponse('subscription not found', Response::HTTP_BAD_REQUEST);

        }

        // get stripe email
        $stripeEmail = $currentUser->getEmail();

        try{

            // get user subscriber
            $customer = $currentUser->getCustomer();

            if($customer){
                $this->checkDataWithStripe($customer);
            }

            // get user subscriber after checking
            $customer = $currentUser->getCustomer();

            // check has plan unlimited, and have user any plans
            if($planId == Subscriber::UNLIMITED){ // refund money, end cancel other subscribes

                if($customer){

                    // update customer in stripe
                    $stripeCustomer = $stripe->updateCustomer($customer->getStripeCustomerId(), $token);
                    unset($stripeCustomer['sources']); // remove cart info
                    unset($stripeCustomer['subscriptions']); // remove plan info
                    $customer->setStripeCustomer($stripeCustomer); // add stripe

                    $paymentsData = $stripe->listSubscription($customer->getStripeCustomerId());

                    // check info
                    if($paymentsData){

                        // get data from invoice
                        $payments = $paymentsData['data'];

                        if(count($payments) > 0){

                            // loop for data
                            foreach($payments as $key => $payment){

                                $stripeSubscriptionId = $payment['id']; // get  subscription id
                                $stripePlan = $payment['plan']; // get plan
                                $stripePlanId = $stripePlan['id']; // get plan id
                                $periodStart = $payment['current_period_start']; // get start date
                                $periodStart = new \DateTime("@$periodStart"); // generate to dataTime object
                                $periodEnd = $payment['current_period_end']; // // get end date
                                $periodEnd = new \DateTime("@$periodEnd"); // generate to dataTime object
                                $currentDate = new \DateTime(); // get current date
                                $currentDate->setTime(23, 59, 59);
                                $firstDay = clone $currentDate; // clone for first day of month
                                $firstDay = $firstDay->modify('first day of this month'); // get first day of month
                                $endDay = clone $currentDate; // clone for last day of month
                                $endDay = $endDay->modify('last day of this month'); // get last day of month

                                // check is payment in current month
                                if(
                                    ($firstDay <= $periodStart && $periodStart <= $endDay ||
                                        $firstDay <= $periodEnd && $periodEnd <= $endDay)
                                    //&& array_key_exists($stripePlanId, $plans)
                                ){
                                    $customer->deleteStripePlan($stripePlanId);
                                    $currentUser->deleteTrialPeriod($stripePlanId);

                                    if($key == 0){
                                        // upgrade
                                        $newSubscription = $stripe->updateSubscription($stripeSubscriptionId, $planId, $couponId);
                                        $customer->addStripePlan($planId, $newSubscription);
                                        $period = $newSubscription['current_period_end'];
                                        $currentUser->setTrialPeriod($planId, $period);
                                    }else{

                                        $charge = $stripe->getInvoiceBySubscribe($customer->getStripeCustomerId(),
                                            $firstDay->getTimestamp(),
                                            $stripeSubscriptionId);

                                        if($charge){
                                            $chargeId = $charge['charge'];
                                            $total = $charge['total'];

                                            $amount = $this->generateAmount($periodEnd, $total);

                                            if($amount > 0){
                                                // refund charge, and cancel subscription
                                                $stripe->refundCharge($chargeId, $amount);
                                            }
                                        }

                                        $stripe->cancelSubscription($stripeSubscriptionId);
                                    }
                                }
                            }
                        }
                        else{
                            // if exist, add subscription to customer
                            $plan = $stripe->createSubscription($customer, $subscription, $couponId);

                            $customer->addStripePlan($planId, $plan);

                            $period = $plan['current_period_end'];
                            $currentUser->setTrialPeriod($planId, $period);

                            $em->persist($customer);
                            $em->persist($currentUser);
                            $em->flush();

                            return $currentUser;
                        }

                    }
                }
                else{
                    // create stripe customer
                    $stripCustomer = $stripe->createCustomer($token, $stripeEmail, $subscription, $couponId);
                    $customer = new Customer();
                    $customer->setUser($currentUser);

                    $plan = $stripCustomer['subscriptions']['data'][0];
                    $customer->addStripePlan($planId, $plan);

                    unset($stripCustomer['sources']);
                    unset($stripCustomer['subscriptions']);
                    $customer->setStripeCustomer($stripCustomer);
                    $period = $plan['current_period_end'];

                    $currentUser->setTrialPeriod($planId, $period);
                }

                $em->persist($customer);
                $em->persist($currentUser);
                $em->flush();

                return $currentUser;
            }
            else{

                // check hav user customer
                if($customer){

                    // update customer in stripe
                    $stripeCustomer = $stripe->updateCustomer($customer->getStripeCustomerId(), $token);

                    // if exist, add subscription to customer
                    $plan = $stripe->createSubscription($customer, $subscription, $couponId);

                }
                else{
                    // create customer in stripe
                    $stripeCustomer = $stripe->createCustomer($token, $stripeEmail, $subscription, $couponId);

                    // create customer in database
                    $customer = new Customer();

                    // set customer to user
                    $customer->setUser($currentUser);

                    // get plan
                    $plan = $stripeCustomer['subscriptions']['data'][0];

                }

                $customer->addStripePlan($planId, $plan);

                unset($stripeCustomer['sources']);
                unset($stripeCustomer['subscriptions']);
                $customer->setStripeCustomer($stripeCustomer);
                $period = $plan['current_period_end'];
                $currentUser->setTrialPeriod($planId, $period);

                $em->persist($customer);
                $em->persist($currentUser);
                $em->flush();

                return $currentUser;

            }
        }
        catch(\Exception $e){
            return new JsonResponse($error = $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Payment",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function postAction(Request $request )
    {
        // get data
        $response = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        // check payment all data
        if($response['type'] == 'coupon.created'){
            $coupon = $response['data']['object'];

            $couponObject = $em->getRepository("LBPaymentBundle:Coupon")->findOneBy(array('couponId' => $coupon['id']));
            if(!$couponObject){
                $couponObject = new Coupon();
                $couponObject->setCouponId($coupon['id']);
                $couponObject->setAmountOff($coupon['amount_off']);
                $couponObject->setDuration($coupon['duration']);
                $couponObject->setDurationInMonth($coupon['duration_in_months']);
                $couponObject->setMaxRedemption($coupon['max_redemptions']);
                $couponObject->setPercentOff($coupon['percent_off']);

                if($coupon['redeem_by']){
                    $redeemBy = $coupon['redeem_by'];
                    $date = new \DateTime("@$redeemBy");
                    $couponObject->setRedeemBy($date);
                }


                $em->persist($couponObject);
                $em->flush();
            }
        }

        // check coupon deleted
        if($response['type'] == 'coupon.deleted'){
            $coupon = $response['data']['object'];

            $couponObject = $em->getRepository("LBPaymentBundle:Coupon")->findOneBy(array('couponId' => $coupon['id']));

            if($couponObject){
                $em->remove($couponObject);
                $em->flush();
            }
        }

        // check payment all data
        if($response['type'] == 'plan.created'){
            $plan = $response['data']['object'];

            $subscribe = $em->getRepository("LBPaymentBundle:Subscriber")->findOneBy(array('stripeId' => $plan['id']));
            if(!$subscribe){
                $subscribe = new Subscriber();
                $subscribe->setStripeId($plan['id']);
                $subscribe->setStripePlan($plan);
                $em->persist($subscribe);
                $em->flush();
            }
        }

        // check payment all data
        if($response['type'] == 'invoice.payment_succeeded'){

           $data = $response['data']['object']; // get data from invoice

            if($data['paid'] === true){ // check is payment paid

                $customerId = $data['customer']; // get customer id

                $planId = $data['lines']['data'][0]['plan']['id']; // get plan id
                $endPeriod = $data['lines']['data'][0]['period']['end']; // get end period as timestamp

                // get subscriber
                $user = $em->getRepository('LBUserBundle:User')->findByCustomerId($customerId);

                // check is exist
                if($user){

                    // set trial period
                    $user->setTrialPeriod($planId, $endPeriod);

                    $em->persist($user);
                    $em->flush();
                }
            }
        }

        // check and delete plan
        if($response['type'] == 'plan.deleted'){
            $data = $response['data']['object'];
            $planId = $data['id'];
            $plan = $em->getRepository("LBPaymentBundle:Subscriber")->findOneBy(array('stripeId' => $planId));
            if($plan){
                $em->remove($plan);
                $em->flush();
            }
        }

        if($response['type'] == 'customer.deleted'){
            $data = $response['data']['object'];
            $customerId = $data['id'];
            $customer = $em->getRepository("LBPaymentBundle:Customer")->findOneBy(array('stripeCustomerId' => $customerId));
            if($customer){
                $user = $customer->getUser(); // get customer user
                $user->deleteTrialPeriod(null, true);
                $em->persist($user);

                $subscriptions = $data['subscriptions']['data']; // get subscriptions
                if(is_array($subscriptions)){
                    foreach ($subscriptions as $subscription){
                        $canceledPlan = $subscription['plan']; // get canceled plan
                        $canceledPlanId = $canceledPlan['id']; // get canceled plan id
                        $customer->deleteStripePlan($canceledPlanId); // check if exist canceled plans in customer plan, and unset it
                        $em->persist($customer);
                    }
                }

                $em->remove($customer);
                $em->flush();
            }
        }

        if($response['type'] == 'customer.subscription.deleted'){
            $data = $response['data']['object'];
            $customerId = $data['customer'];
            $customer = $em->getRepository("LBPaymentBundle:Customer")->findOneBy(array('stripeCustomerId' => $customerId));
            if($customer){
                $user = $customer->getUser(); // get customer user
                $canceledPlan = $data['plan']; // get canceled plan
                $canceledPlanId = $canceledPlan['id']; // get canceled plan id
                $customer->deleteStripePlan($canceledPlanId); // check if exist canceled plans in customer plan, and unset it
                $user->deleteTrialPeriod($canceledPlanId); // delete trial period from user
                $em->persist($customer);
                $em->persist($user);
                $em->flush();
            }

        }

        if($response['type'] == 'customer.subscription.updated'){
            $data = $response['data']['object'];
            $previousPlan =  $response['data']['previous_attributes'];
            $previousPlanId = $previousPlan['plan']['id'];
            $newPlan = $data['plan'];
            $newPlanId = $newPlan['id'];

            $customerId = $data['customer'];
            $customer = $em->getRepository("LBPaymentBundle:Customer")->findOneBy(array('stripeCustomerId' => $customerId));
            if($customer){

                $user = $customer->getUser(); // get customer user

                $user->deleteTrialPeriod($previousPlanId);
                $customer->deleteStripePlan($previousPlanId);

                $customer->addStripePlan($newPlanId, $data);
                $user->setTrialPeriod($newPlanId, $data['current_period_end']);

                $em->persist($customer);
                $em->persist($user);
                $em->flush();
            }

        }

        if($response['type'] == 'customer.subscription.created'){
            $data = $response['data']['object'];
            $customerId = $data['customer'];
            $customer = $em->getRepository("LBPaymentBundle:Customer")->findOneBy(array('stripeCustomerId' => $customerId));
            if($customer){

                $user = $customer->getUser(); // get customer user

                $plan = $data['plan'];
                $planId = $plan['id'];


                $customer->addStripePlan($planId, $data);
                $user->setTrialPeriod($planId, $data['current_period_end']);

                $em->persist($customer);
                $em->persist($user);
                $em->flush();
            }

        }

        if($response['type'] == 'charge.failed'){

            $data = $response['data']['object']; // get data
            $customerId = $data['customer']; // get customer id
            $user = $em->getRepository('LBUserBundle:User')->findByCustomerId($customerId); // get subscriber

            // check user
            if($user){

                // get user email
                $email = $user->getEmail();

                // get twig
                $emailTwig = $this->get('templating')->renderResponse('AppBundle:Blocks:chargeFailEmail.html.twig');

                // send email
                $this->get("app.mandrill")->sendEmail($email, 'luvbyrd.com',
                    'Charge failed in luvbyrd.com', $emailTwig->getContent());
            }
        }

        $monolog = $this->get('monolog.logger.stripe');
        $monolog->info(json_encode($response));

        return new JsonResponse('Success', Response::HTTP_OK);
    }
}