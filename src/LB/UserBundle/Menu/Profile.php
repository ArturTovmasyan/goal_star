<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/29/15
 * Time: 7:21 PM
 */
namespace LB\UserBundle\Menu;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Knp\Menu\FactoryInterface;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class Profile
 * @package LB\UserBundle\Menu
 */
class Profile implements  ContainerAwareInterface
{
    // container aware trait
    use ContainerAwareTrait;

    /**
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function topMenu(FactoryInterface $factory, array $options)
    {
        // default variable for user
        $user = null;

        // get token
        $token = $this->container->get('security.token_storage')->getToken();

//        // check token
//        if($token instanceof UsernamePasswordToken ||
//            $token instanceof OAuthToken ||
//            $token instanceof RememberMeToken ){
//            $user = $token->getUser();
//        }

        // check methods
        if(method_exists($token,'getUser')){
            $user = $token->getUser();
        }

        // default value for admin
        $admin = false;

        // default value for admin
        $oauth = false;

        // get container
        $container = $this->container;

        // get security checker
        $checker = $container->get('security.authorization_checker');

        // check user
        if($user && $user != 'anon.'){
            $oauth = true;

            // check role
            if($checker->isGranted('ROLE_ADMIN')){
                $admin = true;
            }
        }

        $menu = $factory->createItem('root');

        // check is granted as user
        if($admin ){
            $menu->addChild('Dashboard', array('route' => 'sonata_admin_dashboard'));
        }

        if($oauth){
            $menu->addChild('Members', array('route' => 'members'));
        }

//        $menu->addChild('Events', array('route' => 'events'));
        $menu->addChild('Groups', array('route' => 'group_list'));
        $menu->addChild('Why join?', array('route' => 'page', 'routeParameters' => array('slug' => 'why-join')));
        $menu->addChild('How Much?', array('route' => 'how-match'));
        $menu->addChild('Blog', array('route' => 'blog_list'));
        $menu->addChild('Contact', array('route' => 'contact'));

        if($oauth){
            $menu->addChild('Logout', array('route' => 'fos_user_security_logout'));
        }


        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
//        $menu->setChildrenAttribute('class', 'list-inline user-profil-menue');

        $menu->addChild('View Profile', array('route' => 'fos_user_profile_show'));

        $menu->addChild('Edit Profile', array('route' => 'fos_user_profile_edit'));
        $menu['Edit Profile']->addChild('Base', array('route' => 'fos_user_profile_edit'));
        $menu['Edit Profile']->addChild('About Me', array('route' => 'profile_edit_about'));
        $menu['Edit Profile']->addChild('My Activities', array('route' => 'profile_edit_activities'));

        $menu->addChild('My Photos', array('route' => 'profile11', 'routeParameters' => array('name' => 'photos')));
        $menu['My Photos']->addChild('All', array('route' => 'profile11'));
        $menu['My Photos']->addChild('Photos', array('route' => 'profile11'));
        $menu['My Photos']->addChild('Videos', array('route' => 'profile11'));
        $menu['My Photos']->addChild('Music', array('route' => 'profile11'));

        $menu->addChild('Notifications', array('route' => 'profile11', 'routeParameters' => array('name' => 'notes')));
//        $menu->addChild('Friends', array('route' => 'profile_friends', 'routeParameters' => array('status' => UserRelation::DOUBLE_LIKE)));
//        $menu['Friends']->addChild('Double Liked', array('route' => 'profile_friends', 'routeParameters' => array('status' => UserRelation::DOUBLE_LIKE)));
//        $menu['Friends']->addChild('Pending', array('route' => 'profile_friends', 'routeParameters' => array('status' => UserRelation::LIKE)));
//        $menu['Friends']->addChild('Denied Likes', array('route' => 'profile_friends', 'routeParameters' => array('status' => UserRelation::DENIED_LIKE)));

        $menu->addChild('Settings', array('route' => 'fos_user_change_password'));
        if (!$user->getFacebookId()){
            $menu['Settings']->addChild('General', array('route' => 'fos_user_change_password'));
        }
        $menu['Settings']->addChild('Blocked', array('route' => 'profile_blocked'));
        $menu['Settings']->addChild('Disable Account', array('route' => 'disable_account'));
        $menu['Settings']->addChild('Email', array('route' => 'email_settings'));
        $menu['Settings']->addChild('Profile Visibility', array('route' => 'profile_visibility'));


        return $menu;
    }

    /**
     * @param FactoryInterface $factory
     * @param array $options
     * @return \Knp\Menu\ItemInterface
     */
    public function settingsMenu(FactoryInterface $factory, array $options)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $menu = $factory->createItem('root');

        // get visibility
        $visibility = $user->getSearchVisibility();

        $menu->addChild('General', array('route' => 'fos_user_change_password'));
        $menu->addChild('Email', array('route' => 'email_settings'));
        $menu->addChild('Profile Visibility', array('route' => 'profile_visibility'));
        $menu->addChild('Blocked Members', array('route' => 'profile_blocked'));
//        $menu->addChild($visibility === true ? 'Enable Account' : 'Disable Account' , array('route' => 'disable_account'));



        return $menu;
    }
}
