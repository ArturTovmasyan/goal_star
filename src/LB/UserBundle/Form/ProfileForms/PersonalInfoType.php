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
 * Class PersonalInfoType
 * @package LB\UserBundle\Form\ProfileForms
 */
class PersonalInfoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $city = null;
        $data = $builder->getData();

        if($data instanceof User){
            $city = $data->getCity();
        }

        $builder
            ->add('personalInfo', 'textarea', array('required' => false))
            ->add('summary', 'textarea')
            ->add('craziestOutdoorAdventure', 'textarea', array('required' => false))
            ->add('favoriteOutdoorActivity', 'textarea', array('required' => false))
            ->add('likeTryTomorrow', 'textarea', array('required' => false))
            ->add('city', 'text', array('mapped' => false, 'data' => $city))
            ->add('location', 'hidden')
        ;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'lb_user_personal_info';
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => array('personalInfo'),
        ));
    }
}