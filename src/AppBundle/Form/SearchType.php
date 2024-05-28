<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/28/15
 * Time: 12:27 PM
 */

namespace AppBundle\Form;

use AppBundle\Form\DataTransformer\InterestsTransformer;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchType
 * @package AppBundle\Form
 */
class SearchType extends AbstractType
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

        // get all interests
        $interests = $this->container->get('doctrine')->getRepository("AppBundle:interest")->findAll();

        // get all cities
        $cities = array('Yerevan', 'Moscow');


        $builder
            ->add('ageFrom', 'hidden')
            ->add('ageTo', 'hidden')
            ->add('interests', 'interests', array('data'=> $interests))
            ->add('skiAndRide')
            ->add('lookingFor', 'choice', array('choices' => User::$GENDER_CHOICE))
            ->add('city', 'choice', array('choices' => $cities))
            ->add('distance', 'integer', array('attr' =>array('placeholder' => 'MILES')))
            ->add('zipCode', 'text', array('attr' =>array('placeholder' => 'Zip code')))
            ->add('submit', 'submit')
        ;
    }

    public function getName()
    {
        return 'search_type';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Model\SearchData',
        ));
    }
}