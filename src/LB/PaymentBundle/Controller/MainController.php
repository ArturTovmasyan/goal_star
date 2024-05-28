<?php

namespace LB\PaymentBundle\Controller;

use LB\PaymentBundle\Entity\Customer;
use LB\PaymentBundle\Entity\RecurringPaymentDetails;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Extra;

/**
 * @Route("/payment")
 *
 * Class MainController
 * @package LB\PaymentBundle\Controller
 */
class MainController extends Controller
{
    /**
     * @return array
     */
    protected function getSubscriberDetails()
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // find all sy
        $subscriptions = $em->getRepository("LBPaymentBundle:Subscriber")->findAll();

        $result = array();

        foreach ($subscriptions as $subscription) {
            $result[$subscription->getStripeId()] = $subscription;
        }

        return $result;
    }

    /**
     * @Route("/", name="mobile_action")
     * @param Request $request
     * @Template
     * @return array
     */
    public function mobileAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $publishKey = $this->getParameter('stripe_live') ? $this->getParameter('stripe_publish_key_live') : $this->getParameter('stripe_publish_key_sandbox');

        // get all subscribers
        $subscribers = $em->getRepository('LBPaymentBundle:Subscriber')->findAllSubscribers();

        return array('subscribers' => $subscribers, 'publishKey' => $publishKey);
    }

    /**
     * @Route("/payed")
     * @return Response
     */
    public function payedAction()
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        return new Response('ok', Response::HTTP_OK);
    }

    /**
     * @Route("/not-payed")
     * @param Request $request
     * @return JsonResponse
     */
    public function notPayedAction(Request $request)
    {

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get error code
        $errorCode = $request->get('error', null);
        $errorCode = trim($errorCode, '"');

        return new JsonResponse(array('error' => $errorCode), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/prepare_payment_agreement", name="prepare_payment_agreement")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Extra\Template
     */
    public function createAgreementAction(Request $request)
    {
        // find all subscribers
        $subscriptions = $this->getSubscriberDetails();

        // default error
        $error = null;

        $form = $this->createFormBuilder()
            ->add('subscriber', 'choice', array(
                'choices' => $subscriptions, 'label'=>'',
                'required' => true,
                'empty_value' => 'Choose subscribe'
            ))
            ->getForm();

        if ($request->isMethod('POST')) {

            // get data
            $form->handleRequest($request);

            if($form->isValid()){


                // get subscriber type
                $subscriberType = $form->get('subscriber')->getData();
                $subscription = $subscriptions[$subscriberType];
                $token = $request->get('stripeToken');
                $stripeEmail = $request->get('stripeEmail');
                $stripe = $this->container->get('lb.stripe');

                try{

                    // create stripe customer
                    $stripCustomer = $stripe->createCustomer($token, $stripeEmail, $subscription);

                    // get user
                    $currentUser = $this->getUser();

                    // get doctrine
                    $em = $this->getDoctrine()->getManager();

                    $customer = new Customer();
                    $customer->setUser($currentUser);
                    $customer->setStripeCustomer($stripCustomer);

                    $em->persist($customer);
                    $em->flush();

                    return $this->redirectToRoute('membership_details');

                }
                catch(\Exception $e){
                    $error = $e->getMessage();
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'subscriptions' => json_encode($subscriptions),
            'error' => $error
        );
    }

    /**
     * @Route(
     *   "/cancel_recurring_payment/{payum_token}",
     *   name="cancel_recurring_payment"
     * )
     */
    public function cancelRecurringPaymentAction(Request $request)
    {
        $token = $this->getHttpRequestVerifier()->verify($request);
        $this->getHttpRequestVerifier()->invalidate($token);

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        $status = new GetHumanStatus($token);
        $gateway->execute($status);
        if (false == $status->isCaptured()) {
            throw new HttpException(400, 'The model status must be success.');
        }
        if (false == $status->getModel() instanceof RecurringPaymentDetails) {
            throw new HttpException(400, 'The model associated with token not a recurring payment one.');
        }

        /** @var RecurringPaymentDetails $payment */
        $payment = $status->getFirstModel();

        $gateway->execute(new Cancel($payment));
        $gateway->execute(new Sync($payment));

        // get subscriber
        $subscription = $payment->getBuyer()->getSubscriber();

        // set flash message
        $this->setFlashMessageText($request, "member_ship_success", 'membership.cancel_success' ,$subscription->getDescription());

        return $this->redirect($token->getAfterUrl());
    }

    /**
     * Set flashh message
     *
     * @param Request $request
     * @param $name
     * @param $id
     * @param $subscribeValue
     */
    private function setFlashMessageText(Request $request, $name, $id, $subscribeValue)
    {
        // get translator
        $translator = $this->get('translator');

        // translate the text
        $text = $translator->trans($id, array('%subscribe%' => $subscribeValue), 'messages');

        $request->getSession()
            ->getFlashBag()
            ->add($name, $text);
    }
}
