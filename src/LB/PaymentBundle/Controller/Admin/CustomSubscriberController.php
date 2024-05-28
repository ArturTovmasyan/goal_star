<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/19/16
 * Time: 11:29 AM
 */
namespace LB\PaymentBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations\Route;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomSubscriberController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // get users
        $users = $em->getRepository('LBUserBundle:User')->findForSubscribeListBy();

        return $this->render('LBPaymentBundle:Admin:custom_subscriber_list.html.twig', array('users' => $users));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // get request
        $request = $this->container->get('request');

        // create form
        $form = $this->createFormBuilder()
            ->add('user', 'text', array('attr'=> array('class'=>'custom-subscriber-user')))
            ->add('subscribe', 'text', array('attr'=> array('class'=>'custom-subscriber-subscribe')))
            ->add('trialPeriod', 'sonata_type_date_picker', array('format'=>'dd/MM/yyyy'))
            ->getForm();

        if($request->isMethod("POST")){

            // get form
            $form->handleRequest($request);

            // get user id
            $userId = $form->get('user')->getData();

            // get user by id
            $user = $em->getRepository('LBUserBundle:User')->find($userId);

            if(!$user){
                $form->get('user')->addError(new FormError('User not found'));
            }

            // get subscribe id
            $subscribeId = $form->get('subscribe')->getData();

            // get user by id
            $subscribe = $em->getRepository('LBPaymentBundle:Subscriber')->find($subscribeId);

            if(!$subscribe){
                $form->get('subscribe')->addError(new FormError('Subscribe not found'));
            }

            // get trial period
            $trialPeriod = $form->get('trialPeriod')->getData();

            $today = new \DateTime();

            if($trialPeriod <= $today){
                $form->get('trialPeriod')->addError(new FormError('TrialPeriod must be date in future'));
            }

            // check errors
            if($form->isValid()){

                // set user trial period
                $user->setTrialPeriod($subscribe->getStripeId(), $trialPeriod->getTimestamp());
                $user->setHasSimulatePeriod(true);
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('custom-subscriber_list');
            }
        }

        return $this->render('LBPaymentBundle:Admin:custom_subscriber_edit.html.twig',
            array('form'=> $form->createView()));
    }

    /**
     * @param int|null|string $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function deleteAction($id)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        $object = $em->getRepository('LBUserBundle:User')->find($id);

        // check object
        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        // get user customers
        $customers = $object->getCustomer();

        // get stripe plane
        $stripePlan = $customers ?  $customers->getStripePlan() : array();

        // get trial period
        $trialPeriod = $object->getTrialPeriod();

        // loop for trial period
        foreach($trialPeriod as $planId => $period){

            // check is plan in customer
            if(is_array($stripePlan) && count($stripePlan) > 0  && array_key_exists($planId, $stripePlan)){
                continue;
            }
            $object->deleteTrialPeriod($planId);
            $object->setHasSimulatePeriod(false);
        }

        $em->persist($object);
        $em->flush();

        return $this->redirectToRoute('custom-subscriber_list');
    }
}