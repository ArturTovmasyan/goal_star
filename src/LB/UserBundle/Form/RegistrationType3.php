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
class RegistrationType3 extends AbstractType
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
        $inches = range(0,12);
        $feet = array(3 =>3, 4,5,6,7,8,9);

        $builder
            ->add('feet', 'choice', array('required' => true, 'choices' => $feet, 'empty_value'=> 'Feet'))
            ->add('inches', 'choice', array('required' => true, 'choices' => $inches, 'empty_value'=> 'Inches'))
            ->add('zipCode')
            ->add('summary', 'textarea')
            ->add('craziestOutdoorAdventure', 'textarea', array('required' => false))
            ->add('favoriteOutdoorActivity', 'textarea', array('required' => false))
            ->add('likeTryTomorrow', 'textarea', array('required' => false))
            ->add('interests', 'interests')
        ;

        $builder->get('interests')
            ->addModelTransformer(new InterestsTransformer($this->container->get('doctrine')->getManager()));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LB\UserBundle\Entity\User',
            'validation_groups' => array('step3'),
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lb_user_registration_3';
    }
}