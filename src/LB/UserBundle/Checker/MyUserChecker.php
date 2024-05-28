<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/9/15
 * Time: 11:38 AM
 */

namespace LB\UserBundle\Checker;

use LB\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;

/**
 * Class MyUserChecker
 * @package LB\UserBundle\Checker
 */
class MyUserChecker extends UserChecker
{
    /**
     * @var
     */
    private  $container;

    /**
     * MyUserChecker constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AdvancedUserInterface) {
            return;
        }

        if (!$user->isCredentialsNonExpired()) {
            $ex = new CredentialsExpiredException('User credentials have expired.');
            $ex->setUser($user);
            throw $ex;
        }

        if($user instanceof User && $user->getDeactivate() === true){


            $ex = new DisabledException('Your account has been deactivated');
            $ex->setUser($user);
            throw $ex;
        }
    }
}