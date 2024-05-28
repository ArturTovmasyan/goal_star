<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 11:53 AM
 */
namespace AppBundle\Tests\Controller\Rest;

use LB\UserBundle\Tests\Controller\BaseClass;

class UserRelationControllerTest extends BaseClass
{
    /**
     * This function is used to check PostUpdateNewVisitor function in rest
     */
    public function testPostUpdateNewVisitor()
    {
        $url = '/api/v1.0/userrelations/updates/news/visitors';

        // try to update visitor users new status
        $this->clientFrom->request('POST', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not update visitor users new status in postUpdateNewVisitorAction rest!");

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
     * This function is used to check PutFavorite function in rest
     *
     * @depends testPostUpdateNewVisitor
     */
    public function testPutFavorite()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/userrelations/%s/favorites/%s', $userTo->getId(), BaseClass::FAVORITE);

        // try to create new favorite users
        $this->clientFrom->request('PUT', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not create new favorite users in putFavoriteAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetConversation function in rest
     *
     * @depends testPutFavorite
     */
    public function testGetConversation()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/userrelations/%s/conversations/%s', $userTo->getId(), BaseClass::NATIVE);

        // try to change User`s conversation status
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not change User`s conversation status in getConversationAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}