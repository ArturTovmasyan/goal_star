<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/27/15
 * Time: 5:07 PM
 */
namespace LB\UserBundle\Form;

use AppBundle\Form\DataTransformer\InterestsTransformer;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RegistrationType
 * @package LB\UserBundle\Form
 */
class RegistrationType extends AbstractType
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
            ->add('I_am', 'choice', array(
                'choices' => User::$GENDER_CHOICE_FOR_I_AM,
                'expanded' =>true
            ))
            ->add('looking_for', 'choice', array(
                'choices' => User::$GENDER_CHOICE,
                'expanded' =>true,
            ))
            ->add('firstName')
            ->add('lastName')
            ->add('iAgree', 'checkbox', array('label' => 'I agree with the terms and conditions /'))
        ;
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getName()
    {
        return 'lb_user_registration';
    }
}