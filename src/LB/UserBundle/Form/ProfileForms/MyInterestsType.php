<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/16/15
 * Time: 1:56 PM
 */
namespace LB\UserBundle\Form\ProfileForms;

use AppBundle\Form\DataTransformer\InterestsTransformer;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class MyInterestsType
 * @package LB\UserBundle\Form\ProfileForms
 */
class MyInterestsType extends AbstractType
{
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('interests', 'interests')
//            ->add('skyRide', 'textarea', array('label' => 'user.sky_ride', 'required' => true))
        ;

        $builder->get('interests')
            ->addModelTransformer(new InterestsTransformer($this->container->get('doctrine')->getManager()));
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'lb_user_my_interests';
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('myInterest'),
        ));
    }
}