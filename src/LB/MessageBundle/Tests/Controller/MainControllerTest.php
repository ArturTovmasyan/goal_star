<?php

namespace LB\MessageBundle\Tests\Controller;

use LB\UserBundle\Tests\Controller\BaseClass;

class MainControllerTest extends BaseClass
{
//    /**
//     * This function is used to check order of messages
//     */
//    public function testGetUserId()
//    {
//        // try to open messages page
//        $crawler = $this->clientFrom->request('GET', '/messages');
//
//        // Check that the profiler is enabled
//        if ($profile = $this->clientFrom->getProfile()){
//            // check the number of requests
//            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
//        }
//
//        // check first message
//        $link1 = $crawler->filter('a[id="checkMessage"]')->eq(0);
//
//        $user1 = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');
//
//        $this->assertEquals($link1->filter('img')->attr('alt'), $user1->getFirstName(), 'order of messages by created date/time is wrong on messages page!');
//
//        // check second message
//        $link2 = $crawler->filter('a[id="checkMessage"]')->eq(1);
//
//        $user2 = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userMassage3@gmail.com');
//
//        $this->assertEquals($link2->filter('img')->attr('alt'), $user2->getFirstName(), 'order of messages by created date/time is wrong on messages page!');
//
//        // check third message
//        $link3 = $crawler->filter('a[id="checkMessage"]')->eq(2);
//
//        $user3 = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userMassage2@gmail.com');
//
//        $this->assertEquals($link3->filter('img')->attr('alt'), $user3->getFirstName(), 'order of messages by created date/time is wrong on messages page!');
//    }

    /**
     * This function is used to check indexAction action
     */
    public function testIndex()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/messages/%s', $userTo->getUId());

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}
