<?php

namespace AppBundle\Services;

use AppBundle\Model\EmailSettingsData;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpNotFoundException;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Class LbNote
 * @package LB\NotificationBundle\Services
 */

class AppEmail
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
     * This function is used to send email for users
     *
     * @param $toUserId
     * @param $status
     * @param $groupId
     * @throws \Exception
     * @throws \Twig_Error
     */
    public function sendEmail($toUserId, $status, $groupId = null)
    {

        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //get current user
        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        //check if not current user
        if (!$currentUser) {
            // return 400 if currentUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //if userId exist
        if (!(int)$toUserId) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid userId parameters");
        }

        //get to user
        $toUser = $em->getRepository('LBUserBundle:User')->find($toUserId);

        //check if to user not found
        if (!$toUser) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "User by id $toUserId not found");
        }

        // if user is deactivate, return
        if($toUser->isDisable()){
            return;
        }

        //get user name
        $userName = $currentUser->getFirstName();

        //generate user messages link
        $messagesLink = $this->container->get('router')->generate('message_users', array('id' => $toUserId), true);

        //generate user profile link
        $friendRequestLink = $this->container->get('router')->generate('users_like', array(), true);

        //set favorite link
        $favoriteLink = $this->container->get('router')->generate('favorite', array(), true);

        //get web Absolute url
        $webSiteUrl = $this->container->get('router')->generate('homepage', array(), true);

        //get email
//        $toEmail = $toUser->getEmail();

        //get from email in parameter
//        $fromEmail = $this->container->getParameter('to_report_email');

        //get toUser emailSettings data
        $emailSettings = $toUser->getEmailSettings();

        //set default setting value
        $sendEnable = false;

        //set email data
        $data = array(
            'messageLink' => $messagesLink,
            'friendRequestLink' => $friendRequestLink,
            'favoriteLink' => $favoriteLink,
            'status' => $status,
            'userName' => $userName,
            'url' => $webSiteUrl
            );

        //check if email settings exist
        if ($emailSettings) {
            switch ($status) {
                case EmailSettingsData::NEW_MESSAGE:

                    $sendEnable = array_key_exists('newMessage', $emailSettings) ? $emailSettings['newMessage'] : false;
                    break;
                case EmailSettingsData::SEND_FRIEND_REQUEST:

                    $sendEnable = array_key_exists('sendFriendshipRequest', $emailSettings) ? $emailSettings['sendFriendshipRequest'] : false;
                    break;
                case EmailSettingsData::ACCEPT_FRIEND_REQUEST:

                    $sendEnable = array_key_exists('acceptFriendshipRequest', $emailSettings) ? $emailSettings['acceptFriendshipRequest'] : false;
                    break;
                case EmailSettingsData::GROUP_UPDATE:

                    $sendEnable = array_key_exists('groupInfoUpdate', $emailSettings) ? $emailSettings['groupInfoUpdate'] : false;
                    break;
                case EmailSettingsData::JOIN_GROUP:

                    $sendEnable = array_key_exists('joinGroup', $emailSettings) ? $emailSettings['joinGroup'] : false;
                    break;
                case EmailSettingsData::PROMOTED_GROUP:

                    $sendEnable = array_key_exists('promotedAdminOrModerGroup', $emailSettings) ? $emailSettings['promotedAdminOrModerGroup'] : false;
                    break;
                case EmailSettingsData::JOIN_PRIVATE_GROUP:

                    $sendEnable = array_key_exists('requestJoinAdminGroup', $emailSettings) ? $emailSettings['requestJoinAdminGroup'] : false;
                    break;
                default:
                    $sendEnable = false;
            }
        }

        $mandrill = $this->container->get("app.mandrill");

        //check if send email enabled
        if($sendEnable == true) {

            $template = $this->container->get('templating')->render('AppBundle:Main:email.html.twig',
                array('data' => $data));

            // get name
            $name = $toUser->getFirstName() . ' ' . $toUser->getLastName();

            // get email
            $email = $toUser->getEmail();

            // send email via mandrill
            $mandrill->sendEmail($email, $name, 'luvbyrd', $template);

//            // send message
//            $message = \Swift_Message::newInstance()
//                ->setSubject('LUVBYRD')
//                ->setFrom($fromEmail)
//                ->setTo($toEmail)
//                ->setContentType('text/html; charset=UTF-8')
//                ->setBody($this->container->get('templating')->render('AppBundle:Main:email.html.twig',
//                    array('data' => $data)), 'text/html');
//
//            $this->container->get('mailer')->send($message);

        }
    }

}