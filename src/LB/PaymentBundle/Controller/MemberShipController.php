<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 3/16/16
 * Time: 12:41 PM
 */

namespace LB\PaymentBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/membership")
 *
 * Class MemberShipController
 * @package LB\PaymentBundle\Controller
 */
class MemberShipController extends Controller
{
//    /**
//     * @Route("/", name="membership_details")
//     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
//     * @param Request $request
//     * @Template()
//     */
//
//    public function listAction(Request $request)
//    {
////        // get current user
////        $user = $this->getUser();
////
////        $trialPeriod = $user->getTrialPeriod();
////
//////        $user->setTrialPeriod('test47', 5456454);
//////        $this->getDoctrine()->getManager()->persist($user);
//////        $this->getDoctrine()->getManager()->flush($user);
////
////        $customers = $user->getCustomers();
////        $activePayments = array();
////
////        // loop fort customers
////        foreach($customers as $customer){
////            $stripeCustomer = $customer->getStripeCustomer()['subscriptions']['data'][0];
////            $period = $stripeCustomer['current_period_end'];
////            $plan = $stripeCustomer['plan']['name'];
////            $activePayments[] = array('name' => $plan, 'period' => $period);
////        }
//        return array('activePayments' => $activePayments);
//    }
}