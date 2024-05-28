<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/30/15
 * Time: 6:12 PM
 */

namespace LB\UserBundle\Util;

use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/**
 * Class MyPasswordEncoder
 * @package LB\UserBundle\Util
 */
class MyPasswordEncoder implements PasswordEncoderInterface
{
    /**
     * @var
     */
    public $container;

    /**
     * @param Container $container
     */
    function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * @param string $raw
     * @param string $salt
     * @return string
     */
    public function encodePassword($raw, $salt)
    {
        $pl = new MessageDigestPasswordEncoder();

        return $pl->encodePassword($raw, $salt);
    }

    /**
     * @param string $encoded
     * @param string $raw
     * @param string $salt
     * @return bool
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {

        // check password in wordpress encrypt
        if($this->checkPassword($raw, $encoded)){
            return $this->checkPassword($raw, $encoded);
        }

        return $encoded === $this->encodePassword($raw, $salt);
    }

    /**
     * @param $plainPassword
     * @param $hesh
     * @return bool
     */
    private function checkPassword($plainPassword, $hesh)
    {

        $wpHasher = new PasswordHash(8, TRUE);

        return $wpHasher->checkPassword($plainPassword, $hesh);

    }

}