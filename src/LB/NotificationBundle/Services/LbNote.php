<?php

namespace LB\NotificationBundle\Services;

use LB\NotificationBundle\Entity\Notification;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserRelation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpNotFoundException;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Class LbNote
 * @package LB\NotificationBundle\Services
 */

class LbNote
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
     * @param $userId
     * @param $status
     * @param $data
     * @throws
     */
    public function sendNote($userId, $status, $data)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //get translator
        $tr = $this->container->get('translator');

        //get current user
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        //check if not current user
        if(!is_object($currentUser)) {
            // return 400 if currentUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //check if users ids is equal
        if($userId == $currentUser->getId()) {
            // return 400 if users ids equal
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Users cannot send note for itself");
        }

        //if userId exist
        if(!(int)$userId) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid userId parameters");
        }

        //get to user
        $toUser = $em->getRepository('LBUserBundle:User')->find($userId);

        //check if to user not found
        if(!$toUser) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "User by id $userId not found");
        }

        //set default content
        $content = null;

        //generate user group link
        $groupLink = $this->container->get('router')->generate('group_view', array('slug' => $data['slug']), true);

        $groupLink = str_replace('//www.', '//', $groupLink);

        //check if status exist
            switch ($status) {
                case (Notification::INVITE) :

                    $content = "{$currentUser->getShowName()} has invited you to join a group {$data['name']} on {$data['evDate']}";
                    break;
                case (Notification::CONFIRM) :

                    $content = "{$currentUser->getShowName()} has confirmed your request for the group {$data['name']} on {$data['evDate']}";
                    break;
                case (Notification::REQUEST_TO_ADMIN) :

                    $content = "{$currentUser->getShowName()} has sent you a request to join a group {$data['name']}";
                    break;
                case (Notification::CONFIRM_FOR_ADMIN) :

                    $content = "{$currentUser->getShowName()} has confirmed your request for the group {$data['name']} on {$data['evDate']}";
                    break;
                case (Notification::REMOVE) :

                    $content = "{$currentUser->getShowName()} has removed you from the {$data['name']} group";
                    break;
                default :
                    $content = 'invalid parameter';
            }

        // create notification
        $notification = new Notification();
        $notification->setFromUser($currentUser);
        $notification->setToUser($toUser);
        $notification->setStatus($status);
        $notification->setContent($content);
        $notification->setLink($groupLink);

        $em->persist($notification);
        $em->flush();
    }

    /**
     * This function is used to create new visitor user
     *
     */
    public function createVisitor(User  $toUser)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //get current user
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        //check if not current user
        if(!$currentUser) {
            // return 400 if currentUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //check if users ids is equal
        if($toUser->getId() == $currentUser->getId()) {
            // return 400 if users ids equal
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Users cannot send note for itself");
        }

        // create notification
        $userRelation = new UserRelation();
        $userRelation->setFromUser($currentUser);
        $userRelation->setToUser($toUser);
        $userRelation->setFromVisitorStatus(UserRelation::NEW_VISITOR);
        $userRelation->setToVisitorStatus(UserRelation::NATIVE);

        $em->persist($userRelation);
        $em->flush();

        return $userRelation;
    }

    /**
     * This function is used to create new visitor user
     *
     * @param $toUserId
     */
    public function createFavorite($toUserId)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //get current user
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        //check if not current user
        if(!$currentUser) {
            // return 400 if currentUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //check if users ids is equal
        if($toUserId == $currentUser->getId()) {
            // return 400 if users ids equal
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Users cannot send note for itself");
        }

        //if userId exist
        if(!(int)$toUserId) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid userId parameters");
        }

        //get to user
        $toUser = $em->getRepository('LBUserBundle:User')->find($toUserId);

        //check if to user not found
        if(!$toUser) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "User by id $toUserId not found");
        }

        // create notification
        $userRelation = new UserRelation();
        $userRelation->setFromUser($currentUser);
        $userRelation->setToUser($toUser);
        $userRelation->setFromFavoriteStatus(UserRelation::NEW_FAVORITE);
        $userRelation->setToFavoriteStatus(UserRelation::NATIVE);

        $em->persist($userRelation);
        $em->flush();
    }
}