<?php

namespace LB\MessageBundle\Tests\Controller\Rest;

use LB\UserBundle\Tests\Controller\BaseClass;

class MessageControllerTest extends BaseClass
{
    /**
     * This function is used to check GetUserId function in rest
     */
    public function testGetUserId()
    {
        $url = '/api/v1.0/message/user/id';

        // try to get current user id
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get current user id in getUserIdAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $contents, 'Invalid id key in messages getUserIdAction rest json structure');
        $this->assertArrayHasKey('first_name', $contents, 'Invalid first_name key in messages getUserIdAction rest json structure');
        $this->assertArrayHasKey('last_name', $contents, 'Invalid last_name key in messages getUserIdAction rest json structure');
        $this->assertArrayHasKey('profile_image_path', $contents, 'Invalid profile_image_path key in messages getUserIdAction rest json structure');
        $this->assertArrayHasKey('message_image', $contents, 'Invalid message_image key in messages getUserIdAction rest json structure');
        $this->assertEmpty($contents['last_name'], 'last_name value is not empty in getUserIdAction rest json structure');
    }

    /**
     * This function is used to check GetLimitedMessages function in rest
     *
     * @depends testGetUserId
     */
    public function testGetLimitedMessages()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/messages/%s/limiteds/%s/messages/%s', $userTo->getId(), 1, 20 );

        // try to get messages
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get messages in getLimitedMessagesAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in messages getLimitedMobileAction rest json structure');
        $this->assertArrayHasKey('subject', $content, 'Invalid subject key in messages getLimitedMobileAction rest json structure');
        $this->assertArrayHasKey('content', $content, 'Invalid content key in messages getLimitedMobileAction rest json structure');
        $this->assertArrayHasKey('is_read', $content, 'Invalid is_read key in messages getLimitedMobileAction rest json structure');
        $this->assertArrayHasKey('created', $content, 'Invalid created key in messages getLimitedMobileAction rest json structure');

        if(array_key_exists('from_user', $content)) {
            //get fromUser data in content array
            $fromUser = $content['from_user'];

            $this->assertArrayHasKey('id', $fromUser, 'Invalid id key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('profile_image_path', $fromUser, 'Invalid profile_image_path key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $fromUser, 'Invalid profile_image_path_for_mobile key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('first_name', $fromUser, 'Invalid first_name key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('message_image', $fromUser, 'Invalid message_image key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('last_name', $fromUser, 'Invalid last_name key in messages getLimitedMobileAction rest json structure');
            $this->assertEmpty($fromUser['last_name'], 'last_name value is not empty in getLimitedMobileAction rest json structure');

            if(array_key_exists('activity', $fromUser)) {
                //get activity data in fromUser array
                $activity = $fromUser['activity'];

                $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('title', $activity, 'Invalid title key in messages getLimitedMobileAction rest json structure');
            }
        }

        if(array_key_exists('from_user', $content)) {
            //get fromUser data in content array
            $toUser = $content['to_user'];

            $this->assertArrayHasKey('id', $toUser, 'Invalid id key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('profile_image_path', $toUser, 'Invalid profile_image_path key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $toUser, 'Invalid profile_image_path_for_mobile key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('first_name', $toUser, 'Invalid first_name key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('message_image', $toUser, 'Invalid message_image key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('last_name', $toUser, 'Invalid last_name key in messages getLimitedMobileAction rest json structure');
            $this->assertEmpty($toUser['last_name'], 'last_name value is not empty in getLimitedMobileAction rest json structure');

            if(array_key_exists('activity', $toUser)) {
                //get activity data in fromUser array
                $activity = $toUser['activity'];

                $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('title', $activity, 'Invalid title key in messages getLimitedMobileAction rest json structure');
            }
        }
    }

    /**
     * This function is used to check GetEmail function in rest
     *
     * @depends testGetLimitedMessages
     */
    public function testGetEmail()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/messages/%s/email', $userTo->getId());

        // try to send email
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not send email in getEmailAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetMessageUsers function in rest
     *
     * @depends testGetEmail
     */
    public function testGetMessageUsers()
    {
        $url = '/api/v1.0/message/user/id';

        // try to get user list with last message
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get user list with last message in getMessageUsersAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $contents, 'Invalid id key in messages getMessageUsersAction rest json structure');
        $this->assertArrayHasKey('first_name', $contents, 'Invalid first_name key in messages getMessageUsersAction rest json structure');
        $this->assertArrayHasKey('last_name', $contents, 'Invalid last_name key in messages getMessageUsersAction rest json structure');
        $this->assertArrayHasKey('profile_image_path', $contents, 'Invalid profile_image_path key in messages getMessageUsersAction rest json structure');
        $this->assertArrayHasKey('message_image', $contents, 'Invalid message_image key in messages getMessageUsersAction rest json structure');
        $this->assertEmpty($contents['last_name'], 'last_name value is not empty in getMessageUsersAction rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check getLimitedMobileAction function in rest
     *
     */
    public function testGetLimitedMobile()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/messages/%s/limited/mobile?_format=json&start=0&count=1', $userTo->getId());

        // try to get user list with last message
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get user list with last message in getLimitedMobileAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('isFavorite', $contents, 'Invalid isFavorite key in messages getLimitedMobileAction rest json structure');

        if(array_key_exists('messages', $contents)) {
            //get messages data in array
            $messages = $contents['messages'];

            $message = reset($messages);

            $this->assertArrayHasKey('id', $message, 'Invalid id key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('subject', $message, 'Invalid subject key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('content', $message, 'Invalid content key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('is_read', $message, 'Invalid is_read key in messages getLimitedMobileAction rest json structure');
            $this->assertArrayHasKey('created', $message, 'Invalid created key in messages getLimitedMobileAction rest json structure');

            if(array_key_exists('from_user', $message)) {
                //get fromUser data in message array
                $fromUser = $message['from_user'];

                $this->assertArrayHasKey('id', $fromUser, 'Invalid id in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('age', $fromUser, 'Invalid age in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('profile_image_path_for_mobile', $fromUser, 'Invalid profile_image_path_for_mobile in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('only_city', $fromUser, 'Invalid only_city in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('first_name', $fromUser, 'Invalid first_name in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('city', $fromUser, 'Invalid city in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('_i_am', $fromUser, 'Invalid _i_am in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('notification_switch', $fromUser, 'Invalid notification_switch in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('message_image', $fromUser, 'Invalid message_image in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('last_name', $fromUser, 'Invalid last_name in messages getLimitedMobileAction rest json structure');
                $this->assertEmpty($fromUser['last_name'], 'last_name value is not empty in getLimitedMobileAction rest json structure');

                if(array_key_exists('permission', $fromUser)) {
                    //get permission data in fromUser array
                    $permission = $fromUser['permission'];

                    $this->assertArrayHasKey('luvbyrd_unlimited', $permission, 'Invalid luvbyrd_unlimited in messages getLimitedMobileAction rest json structure');
                }

                if(array_key_exists('activity', $fromUser)) {
                    //get activity data in fromUser array
                    $activity = $fromUser['activity'];

                    $this->assertArrayHasKey('minute', $activity, 'Invalid minute in messages getLimitedMobileAction rest json structure');
                    $this->assertArrayHasKey('title', $activity, 'Invalid title in messages getLimitedMobileAction rest json structure');
                }
            }

            if(array_key_exists('to_user', $message)) {
                //get toUser data in message array
                $toUser = $message['to_user'];

                $this->assertArrayHasKey('id', $toUser, 'Invalid id in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('age', $toUser, 'Invalid age in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('profile_image_path_for_mobile', $toUser, 'Invalid profile_image_path_for_mobile in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('only_city', $toUser, 'Invalid only_city in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('first_name', $toUser, 'Invalid first_name in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('city', $toUser, 'Invalid city in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('_i_am', $toUser, 'Invalid _i_am in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('notification_switch', $toUser, 'Invalid notification_switch in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('message_image', $toUser, 'Invalid message_image in messages getLimitedMobileAction rest json structure');
                $this->assertArrayHasKey('last_name', $toUser, 'Invalid last_name in messages getLimitedMobileAction rest json structure');
                $this->assertEmpty($toUser['last_name'], 'last_name value is not empty in getLimitedMobileAction rest json structure');

                if(array_key_exists('permission', $toUser)) {
                    //get permission data in fromUser array
                    $permission = $toUser['permission'];

                    $this->assertArrayHasKey('luvbyrd_unlimited', $permission, 'Invalid luvbyrd_unlimited in messages getLimitedMobileAction rest json structure');
                }

                if(array_key_exists('activity', $toUser)) {
                    //get activity data in fromUser array
                    $activity = $toUser['activity'];

                    $this->assertArrayHasKey('minute', $activity, 'Invalid minute in messages getLimitedMobileAction rest json structure');
                    $this->assertArrayHasKey('title', $activity, 'Invalid title in messages getLimitedMobileAction rest json structure');
                }
            }
        }
    }

    /**
     * This function is used to check Delete function in rest
     *
     * @depends testGetMessageUsers
     */
    public function testDelete()
    {
        // get notification
        $message = $this->em->getRepository('LBMessageBundle:Message')->findOneBySubject('message');

        $url = sprintf('/api/v1.0/messages/%s', $message->getId());

        // try to get delete message
        $this->clientFrom->request('DELETE', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not delete message in deleteAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}
