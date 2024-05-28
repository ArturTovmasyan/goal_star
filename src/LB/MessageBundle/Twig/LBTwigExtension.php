<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 11/6/15
 * Time: 1:02 PM
 */
namespace LB\MessageBundle\Twig;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LBTwigExtension extends \Twig_Extension
{
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('last_message', array($this, 'lastMessage')),
            new \Twig_SimpleFunction('conversation', array($this, 'conversation')),
        );
    }

    /**
     * @param $userId
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function lastMessage($userId)
    {
        $token = $this->container->get('security.token_storage')->getToken();
        if (!$token){
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        $em = $this->container->get('doctrine')->getManager();
        $lastMessage = $em->getRepository('LBMessageBundle:Message')->getUsersLastMessage($token->getUser()->getId(), $userId);

        return $lastMessage;
    }

    /**
     * This function is used to unread user favorite count
     *
     * @return int
     */
    public function conversation()
    {
        //get current userId
        $userId = $this->container->get('security.token_storage')->getToken()->getUser()->getId();

        if (!$userId){
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        //get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //get new favorite users count
        $conversations = $em->getRepository("LBUserBundle:User")->findConversations($userId);

        return $conversations;
    }

    public function getName()
    {
        return 'last_message';
    }
}