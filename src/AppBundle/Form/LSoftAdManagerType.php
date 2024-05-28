<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/31/16
 * Time: 4:11 PM
 */
namespace AppBundle\Form;

use AppBundle\Form\Type\LocationType;
use LB\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LSoftAdManagerType
 * @package AppBundle\Form
 */
class LSoftAdManagerType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('gender', ChoiceType::class,
                array('choices' => User::$GENDER_CHOICE_FOR_I_AM, 'empty_value'=> 'All', 'required' => false))
            ->add('minAge')
            ->add('maxAge')
            ->add('city', LocationType::class, array('required' => false))
            ->add('interests')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\LSoftAdManager',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'l_soft_ad_manager_type';
    }
}