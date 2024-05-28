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
 * Class BasicInfoType
 * @package LB\UserBundle\Form\ProfileForms
 */
class BasicInfoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $inches = range(0,12);
        $feet = array(3 =>3, 4,5,6,7,8,9);

        $builder
            ->add('birthday', 'date', array(
                'years' => range(date('Y')-90, date('Y')-18)
            ))
            ->add('I_am', 'choice', array(
                'choices' => User::$GENDER_CHOICE_FOR_I_AM,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('looking_for', 'choice', array(
                'choices' => User::$GENDER_CHOICE,
                'expanded' => true,
            ))
            ->add('feet', 'choice', array('choices' => $feet, 'empty_value'=> 'Feet'))
            ->add('inches', 'choice', array('choices' => $inches, 'empty_value'=> 'Inches'))
        ;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'lb_user_basic_info';
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('basicInfo'),
        ));
    }
}