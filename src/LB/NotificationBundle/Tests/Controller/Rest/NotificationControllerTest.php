<?php

namespace LB\NotificationBundle\Tests\Controller\Rest;

use LB\NotificationBundle\Entity\Notification;
use LB\UserBundle\Tests\Controller\BaseClass;

class NotificationControllerTest extends BaseClass
{
    /**
     * This function is used to check GetAllNoteByUser function in rest
     */
    public function testGetAllNoteByUser()
    {
        $url = '/api/v1.0/notification/all/note/by/user';

        // try to get all notification by user
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get all notification by user in getAllNoteByUserAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in notification getAllNoteByUserAction rest json structure');
        $this->assertArrayHasKey('status', $content, 'Invalid status key in notification getAllNoteByUserAction rest json structure');
        $this->assertArrayHasKey('content', $content, 'Invalid content key in notification getAllNoteByUserAction rest json structure');
        $this->assertArrayHasKey('is_read', $content, 'Invalid is_read key in notification getAllNoteByUserAction rest json structure');
        $this->assertArrayHasKey('created', $content, 'Invalid created key in notification getAllNoteByUserAction rest json structure');
    }

    /**
     * This function is used to check GetNoteByUserIdAndStatus function in rest
     *
     * @depends testGetAllNoteByUser
     */
    public function testGetNoteByUserIdAndStatus()
    {
        $url = sprintf('/api/v1.0/notifications/%s/note/by/user/id/and/status', Notification::CONFIRM);

        // try to get notification by user id and status
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get notification by user id and status in getNoteByUserIdAndStatusAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in notification getNoteByUserIdAndStatusAction rest json structure');
        $this->assertArrayHasKey('status', $content, 'Invalid status key in notification getNoteByUserIdAndStatusAction rest json structure');
        $this->assertArrayHasKey('content', $content, 'Invalid content key in notification getNoteByUserIdAndStatusAction rest json structure');
        $this->assertArrayHasKey('is_read', $content, 'Invalid is_read key in notification getNoteByUserIdAndStatusAction rest json structure');
        $this->assertArrayHasKey('created', $content, 'Invalid created key in notification getNoteByUserIdAndStatusAction rest json structure');

    }

    /**
     * This function is used to check GetCount function in rest
     *
     * @depends testGetNoteByUserIdAndStatus
     */
    public function testGetCount()
    {
        $url = '/api/v1.0/notification/count';

        // try to get count of notifications
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get count of notifications in getCountAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('0', $contents, 'Invalid created key in notification getCountAction rest json structure');
    }

    /**
     * This function is used to check Delete function in rest
     *
     * @depends testGetCount
     */
    public function testDelete()
    {
        // get notification
        $notification = $this->em->getRepository('LBNotificationBundle:Notification')->findOneByStatus(Notification::CONFIRM);

        $url = sprintf('/api/v1.0/notifications/%s', $notification->getId());

        // try to remove all notifications by given user id
        $this->clientFrom->request('DELETE', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not remove all notifications by given user id in deleteAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );
    }
}
