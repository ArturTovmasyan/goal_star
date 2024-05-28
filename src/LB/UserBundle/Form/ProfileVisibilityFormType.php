<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/27/15
 * Time: 5:07 PM
 */
namespace LB\UserBundle\Form;

use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileBaseFormType
 * @package LB\UserBundle\Form
 */
class ProfileVisibilityFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stateVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('zipCodeVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('countryVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('craziestOutdoorAdventureVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('favoriteOutdoorActivityVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ))
            ->add('likeTryTomorrowVisibility', 'choice', array(
                'choices' => array(
                    User::EVERYONE    => 'Everyone',
                    User::ALL_MEMBERS => 'All Members',
                    User::MY_FRIENDS  => 'My Friends',
                    User::ONLY_ME     => 'Only Me'
                )
            ));

    }

    public function getName()
    {
        return 'lb_visibility_profile';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LB\UserBundle\Entity\User',
            'validation_groups' => 'Edit'
        ));
    }
}