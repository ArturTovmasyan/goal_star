<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/27/15
 * Time: 5:07 PM
 */
namespace LB\UserBundle\Form;

use LB\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileBaseFormType
 * @package LB\UserBundle\Form
 */
class ProfileBaseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('lastNameVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('birthday', 'date', array('widget' => 'single_text'))
            ->add('I_am', 'choice', array('empty_value' => '------', 'choices' => array(User::MAN => 'Man', User::WOMAN => 'Woman')))
            ->add('looking_for', 'choice', array(
                'choices' => User::$GENDER_CHOICE,
                'expanded' =>true,
                'multiple' =>true))
            ->add('city')
//            ->add('state')
            ->add('stateVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('zipCode')
            ->add('zipCodeVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('country')
            ->add('countryVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
        ;
    }

    public function getName()
    {
        return 'lb_user_profile';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LB\UserBundle\Entity\User',
        ));
    }
}