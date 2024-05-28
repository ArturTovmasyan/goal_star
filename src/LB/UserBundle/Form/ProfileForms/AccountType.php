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
 * Class AccountType
 * @package LB\UserBundle\Form\ProfileForms
 */
class AccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username')
            ->add('email')
            ->add('firstName')
            ->add('lastName')
        ;
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('accountType'),
        ));
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'lb_user_account';
    }
}