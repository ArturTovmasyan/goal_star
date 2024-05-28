<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/29/15
 * Time: 6:18 PM
 */
namespace LB\UserBundle\Form;

use LB\UserBundle\Form\DataTransformer\EmailSettingsTransformer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailSettingsType
 * @package LB\UserBundle\Form
 */

class EmailSettingsType extends AbstractType
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
            ->add('newMessage', 'checkbox', array(
                'required'=> false,
                'label' => 'A member sends you a new message',
            ))
            ->add('sendFriendshipRequest', 'checkbox', array(
                'required'=> false,
                'label' => 'A member sends you a friendship request',
            ))
            ->add('acceptFriendshipRequest', 'checkbox', array(
                'required'=> false,
                'label' => 'A member accepts your friendship request',
            ))
            ->add('joinGroup', 'checkbox', array(
                'required'=> false,
                'label' => 'A member invites you to join a group',
            ))
            ->add('groupInfoUpdate', 'checkbox', array(
                'required'=> false,
                'label' => 'Group information is updated',
            ))
            ->add('promotedAdminOrModerGroup', 'checkbox', array(
                'required'=> false,
                'label' => 'You are promoted to a group administrator or moderator',
            ))
            ->add('requestJoinAdminGroup', 'checkbox', array(
                'required'=> false,
                'label' => 'A member requests to join a private group for which you are an admin',
            ))
        ;
        $builder
            ->addModelTransformer(new EmailSettingsTransformer($this->container));
    }


    public function getName()
    {
        return 'lb_email_settings_type';
    }
}