<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 7/21/16
 * Time: 2:00 PM
 */
namespace LB\UserBundle\Provider;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * Class ApiKeyUserProvider
 * @package LB\UserBundle\Provider
 */

class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var User
     */
    protected $user = null;

    /**
     * ApiKeyUserProvider constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $apiKey
     * @return mixed
     */
    public function getUsernameForApiKey($apiKey)
    {
        $this->user = $this->em->getRepository('LBUserBundle:User')->findOneByApiKey($apiKey);

        return is_null($this->user) ? null : $this->user->getUsername();
    }

    /**
     * @param string $username
     * @return User
     */
    public function loadUserByUsername($username)
    {
        if (!is_null($this->user) && $this->user->getUsername() == $username){
            return $this->user;
        }

        $this->user = $this->em->getRepository('LBUserBundle:User')->findOneByUsername($username);
        return $this->user;
    }

    /**
     * @param UserInterface $user
     * @return null
     */
    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return 'LB\UserBundle\Entity\User' === $class;
    }
}
