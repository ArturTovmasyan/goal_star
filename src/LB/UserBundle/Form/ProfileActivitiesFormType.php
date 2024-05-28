<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/27/15
 * Time: 5:07 PM
 */
namespace LB\UserBundle\Form;

use AppBundle\Form\DataTransformer\InterestsTransformer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileActivitiesFormType
 * @package LB\UserBundle\Form
 */
class ProfileActivitiesFormType extends AbstractType
{

    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('interests', 'interests')
        ;

        $builder->get('interests')
            ->addModelTransformer(new InterestsTransformer($this->container->get('doctrine')->getManager()));
    }

    public function getName()
    {
        return 'lb_user_profile_activities';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LB\UserBundle\Entity\User',
        ));
    }
}