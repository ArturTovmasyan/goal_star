<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 3/7/16
 * Time: 12:22 PM
 */

namespace LB\PaymentBundle\Admin;

use LB\PaymentBundle\Entity\Subscriber;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class SubscriberAdmin
 * @package LB\PaymentBundle\Admin
 */
class SubscriberAdmin extends Admin
{
//    public function configureRoutes(RouteCollection $collection)
//    {
//        $collection->remove('edit');
//    }

    /**
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('stripeId')
            ->add('name')
            ->add('amount')
            ->add('currency')
            ->add('interval')
            ->add('intervalCount')
            ->add('_action',
                'actions', array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                    )
                ));
    }

    /**
     *
     * @param DatagridMapper $filterMapper
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('stripeId')
        ;
    }

    /**
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();

        $object = $this->getSubject();

        if($object->getId() == null){
            $formMapper
                ->add('stripeId', 'choice', array('choices' => Subscriber::$PLAN, 'empty_value' => 'Choose plan'))
                ->add('amount', 'integer', array('attr' => array("step"=>"0.1")))
                ->add('currency', 'choice', array(
                    'choices' => array(
                        'usd' => 'USD',
                    )
                ))
                ->add('intervalCount', 'integer', array('attr' => array("max"=>"52")))
                ->add('interval',
                    'choice', array(
                        'choices' => array(
                            Subscriber::DAY => 'Day',
                            Subscriber::WEEK => 'Week',
                            Subscriber::MONTH => 'Month',
                            Subscriber::YEAR => 'Year'
                        )
                    ))
            ;
        }

        $formMapper
            ->add('description', 'textarea')
            ;
    }

    public function prePersist($object)
    {
                // get container
        $container = $this->getConfigurationPool()->getContainer();

        $name = Subscriber::$PLAN[$object->getStripeId()];

        // get stripe
        $stripe = $container->get('lb.stripe');

        $plan = array(

            "id" => $object->getStripeId(),
            "amount" => $object->amount * 100,
            "currency" => $object->currency,
            "interval" => $object->interval,
            "interval_count" => $object->intervalCount,
            "name" => $name,
        );

        try{
            // create stripe plan
            $stripe = $stripe->createPlan($plan);

            // serialize strip data
            $params = $stripe->jsonSerialize();

            // set params
            $object->setStripePlan($params);

            // set stripe plan
        }
        catch(\Exception $e){
            throw $e;

        }
    }

    public function preRemove($object)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();

        // get stripe
        $stripe = $container->get('lb.stripe');

        try{
            // create stripe plan
            $stripe->deletePlan($object->getStripeId());

            // set stripe plan
        }
        catch(\Exception $e){
            throw $e;

        }
    }
}