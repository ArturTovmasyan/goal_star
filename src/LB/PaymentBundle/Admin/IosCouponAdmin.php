<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/15/16
 * Time: 12:08 PM
 */

namespace LB\PaymentBundle\Admin;

use LB\PaymentBundle\Entity\Subscriber;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class CouponAdmin
 * @package LB\PaymentBundle\Admin
 */
class IosCouponAdmin extends Admin
{
    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
    }

    /**
     *
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('couponId')
            ->add('durationInDay')
            ->add('deprecated', null, array(
                'editable' => true
            ))
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
            ->add('couponId')
            ->add('durationInDay')
            ->add('deprecated')
        ;
    }

    /**
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper

            ->add('couponId', null, array('required'=>false,'help'=>'Unique string of your choice that will be used to identify this
            coupon when applying it to a customer. This is often a specific code you’ll give to your customer to
            use when signing up (e.g. FALL25OFF). If you don’t want to specify a particular code, you can leave the
             ID blank and we’ll generate a random code for you.'))
            ->add('durationInDay');
    }

    public function prePersist($object)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();

        $appService = $container->get('app.luvbyrd.service');

        $couponId = $object->getCouponId();
        if(!$couponId){
            do {
                $string = $appService->randomString(9);
                $coupon = $container->get('doctrine')->getRepository('LBPaymentBundle:IosCoupon')->findOneBy(array('couponId' => $string));
            } while ($coupon);

            $object->setCouponId($string);
        }

    }

}