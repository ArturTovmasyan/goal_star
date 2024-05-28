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
class CouponAdmin extends Admin
{
    /**
     * @param string $name
     * @return null|string|void
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'LBPaymentBundle:Admin:coupon_edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

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
        ;
    }

    /**
     *
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // duration
        $duration = 'forever';

        if ($this->hasRequest()) {

            $request = $this->getRequest();
            $form = $request->get($this->getUniqid());
            $duration = (is_array($form) && array_key_exists('duration', $form)) ? $form['duration']: $duration;
        }

            $formMapper

                ->add('couponId', null, array('help'=>'Unique string of your choice that will be used to identify this
                coupon when applying it to a customer. This is often a specific code you’ll give to your customer to
                use when signing up (e.g. FALL25OFF). If you don’t want to specify a particular code, you can leave the
                 ID blank and we’ll generate a random code for you.'))

                ->add('duration', 'choice', array('choices'=>array(
                    'forever' => 'Forever',
                    'once' => 'Once',
                    'repeating' => 'Repeating'
                ),
                    'attr' => array('data-sonata-select2'=>'false', 'ng-model' => 'duration', 'class'=>' form-control',  'ng-init'=> "duration='$duration'"),
                    'help'=>'Specifies how long the discount will be in effect',
                    'label' => 'Duration (REQUIRED) '
                ))

                ->add('durationInMonth', null, array(
                    'label_attr'=> array('ng-show' => "duration=='repeating'"),
                    'sonata_help'=>'Iit must be a positive integer that specifies
                     the number of months the discount will be in effect.',
                    'label' => 'DurationInMonth (REQUIRED) ',
                    'attr' => array('ng-show' => "duration=='repeating'"),
                    ))

                ->add('percentOff', null, array('help'=>'A positive integer between 1 and 100 that represents the
                discount the coupon will apply',
                    'label' => 'PercentOff (required if amount_off is not passed)'
                    ))

                ->add('amountOff', null, array('help'=>'A positive integer representing the amount to subtract from an
                invoice total',
                    'label' => 'PercentOff (required if percent_off is not passed) '
                    ))

                ->add('maxRedemption', null, array('help'=>'A positive integer specifying the number of times the coupon
                 can be redeemed before it’s no longer valid. For example, you might have a 50% off coupon that the first
                  20 readers of your blog can use.',
                    'label' => 'Maximum Redemption (Optional) '))

                ->add('redeemBy', 'sonata_type_date_picker', array('required' => false, 'help'=>'Specifying the last
                time at which the coupon can be redeemed.
                 After the redeem_by date, the coupon can no longer be applied to new customers',
                    'label' => 'Redeem By (Optional) '
                    ))
            ;
    }

    public function prePersist($object)
    {
        // get container
        $container = $this->getConfigurationPool()->getContainer();

        // get stripe
        $stripe = $container->get('lb.stripe');

        $coupon = array(

            "id" => $object->getCouponId(),
            "currency" => $object->currency,
            "duration" => $object->getDuration(),
        );

        // check redeem by
        if($object->getDuration() == 'repeating'){
            $coupon['duration_in_months'] = $object->getDurationInMonth();
        }

        // check percent off
        if($object->getAmountOff()){
            $coupon['amount_off'] = $object->getAmountOff();
        }

        // check amount off
        if($object->getPercentOff()){
            $coupon['percent_off'] = $object->getPercentOff();
        }

        // check Max Redemption
        if($object->getMaxRedemption()){
            $coupon['max_redemptions'] = $object->getMaxRedemption();
        }

        // check redeem by
        if($object->getMaxRedemption()){
            $coupon['redeem_by'] = $object->getRedeemBy()->getTimestamp();
        }

        try{
            // create coupon
            $stripe->createCoupon($coupon);

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
            $stripe->deleteCoupon($object->getCouponId());

            // set stripe plan
        }
        catch(\Exception $e){
            throw $e;

        }
    }
}