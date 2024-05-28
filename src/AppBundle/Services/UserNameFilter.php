<?php

namespace AppBundle\Services;

use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpNotFoundException;

/**
 * Class UserNameFilter
 * @package AppBundle\Services
 */

class UserNameFilter
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * This function is used to filter full name
     *
     * @param $user
     * @return mixed
     */
    public function fullNameFilter($user)
    {
        if($user && $user instanceof User){
            $fullName = $user->getFirstName();

//            $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

//            if($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || (is_object($currentUser) && $user->getId() == $currentUser->getId()) ){
            if($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ){
                $fullName = $fullName.' '.$user->getLastName();
            }
            return $fullName ;
        }
        return null;

    }
}