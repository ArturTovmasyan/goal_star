<?php

namespace AppBundle\Form;

use AppBundle\Model\ContactData;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ContactType
 * @package AppBundle\Form
 */
class ContactType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array("WEB" => "Web", "Android" => "Android", "IOS" => "IOS");

        $builder
            ->add('name', 'text', array('label' => 'contact.form.name', 'required' => true))
            ->add('email', 'email', array('label' => 'contact.form.email', 'required' => true))
            ->add('subject', 'text', array('label' => 'contact.form.subject'))
            ->add('userName', 'text', array('label' => 'contact.form.username', 'required' => true))
            ->add('device', 'choice', array('choices'=> $choices,'empty_value' => 'Choose your device', 'choices_as_values'=>true, 'label' => 'contact.form.device', 'required' => true))
            ->add('message', 'textarea', array('label' => 'contact.form.your_message', 'required' => true))
        ;
    }

    public function getName()
    {
        return 'contact_type';
    }
}