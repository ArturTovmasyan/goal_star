<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/27/15
 * Time: 5:30 PM
 */
namespace AppBundle\Form\Type;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LeadLimit
 * @package AppBundle\Form\Type
 */
class Interests extends AbstractType
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
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'text';
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'interests';
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $em = $this->container->get('doctrine')->getManager();
        $interestGroups = $em->getRepository('AppBundle:InterestGroup')->findAllOrderByPosition();
        $view->vars['interestGroups'] = $interestGroups;
    }
}
