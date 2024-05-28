<?php

namespace AppBundle\Tests\Controller;

use LB\UserBundle\Entity\UserRelation;
use Symfony\Component\HttpFoundation\Response;

class MainControllerTest extends BaseClass
{
    /**
     * This function is used to check sitemap
     */
    public function testSitemapXml()
    {
        // try to open page
        $this->clientFrom->request('GET', '/sitemap.xml');

        //get content in response
        $content = $this->clientFrom->getResponse()->getContent();

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open sitemap.xml");

        //get xml data in content
        $xml = simplexml_load_string($content);

        //json_encode xml data
        $xmlDataEncode = json_encode($xml);

        //json_decode xml data for get json structure
        $xmlData = json_decode($xmlDataEncode, true);

        //get sitemap array
        $siteMap = $xmlData['sitemap'];

        $this->assertArrayHasKey('loc', $siteMap, 'Invalid loc key in sitemap.xml response');
        $this->assertArrayHasKey('lastmod', $siteMap, 'Invalid lastmod key in sitemap.xml response');

        //get sitemap location
        $siteMapLoc = $siteMap['loc'];

        //get array data for deafult sitemap.xml url
        $xmlPath = parse_url($siteMapLoc);

        //get default sitemap path
        $defaultXmlPath = $xmlPath['path'];

        // try to open page
        $this->clientFrom->request('GET', $defaultXmlPath);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open sitemap.default.xml");

        //get content in response
        $defaultXmlData = $this->clientFrom->getResponse()->getContent();

        //get xml data in content
        $xml = simplexml_load_string($defaultXmlData);

        //json_encode xml data
        $defaultXmlDataEncode = json_encode($xml);

        //json_decode xml data for get json structure
        $xmlData = json_decode($defaultXmlDataEncode, true);

        if(array_key_exists('url', $xmlData)) {

            //get default sitemap data
            $defaultXmlArrays = $xmlData['url'];

            foreach ($defaultXmlArrays as $defaultXmlArray)
            {
                $this->assertArrayHasKey('loc', $defaultXmlArray, 'Invalid loc key in sitemap.default.xml response');
                $this->assertArrayHasKey('lastmod', $defaultXmlArray, 'Invalid lastmod key in sitemap.default.xml response');
                $this->assertArrayHasKey('changefreq', $defaultXmlArray, 'Invalid changefreq key in sitemap.default.xml response');
                $this->assertArrayHasKey('priority', $defaultXmlArray, 'Invalid priority key in sitemap.default.xml response');
            }
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check homepage
     */
    public function testIndex()
    {
        // try to open homepage
        $this->client->request('GET', '/');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not open homepage!");

        // Check that the profiler is enabled
        if ($profile = $this->client->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on homepage!");
        }
    }

    /**
     * This function is used to check login page
     */
    public function testLogin()
    {
        // try to open login page
        $this->client->request('GET', '/login');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not open login page!");

        // Check that the profiler is enabled
        if ($profile = $this->client->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on login page!");
        }
    }

    /**
     * This function is used to check members page
     */
    public function testMembers()
    {
        // try to open members page
        $this->clientFrom->request('GET', '/members/');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open members page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on members page!");
        }

        // get user
        $user = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user@gmail.com');

        // get username
        $userName = $user->getUserName();

        // Assert that the response content contains a username
        $this->assertContains($userName, $this->clientFrom->getResponse()->getContent(), "can not find username!");
    }

    /**
     * This function is used to check connection page
     */
    public function testConnection()
    {
        // try to open connection page
        $this->clientFrom->request('GET', '/connections');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open connection page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on connection page!");
        }
    }

    /**
     * This function is used to check like page
     */
    public function testLike()
    {
        // try to open like page
        $this->clientFrom->request('GET', '/like');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open like page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on like page!");
        }
    }

    /**
     * This function is used to check favorite page
     */
    public function testFavorite()
    {
        // try to open favorite page
        $this->clientFrom->request('GET', '/favorite');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open favorite page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on favorite page!");
        }
    }

    /**
     * This function is used to check visitor page
     */
    public function testVisitor()
    {
        // try to open visitor page
        $this->clientFrom->request('GET', '/visitor');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open visitor page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on visitor page!");
        }
    }

    /**
     * This function is used to check messages page
     */
    public function testMessages()
    {
        // try to open visitor page
        $this->clientFrom->request('GET', '/messages');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open messages page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on messages page!");
        }
    }

    /**
     * This function is used to check page action
     */
    public function testPage()
    {
        // get page
        $page = $this->em->getRepository('AppBundle:Page')->findOneByTitle('page1');

        // try to open page
        $this->clientFrom->request('GET', '/' . $page->getSlug() . '/');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check contact page
     */
    public function testContact()
    {
        // try to open contact page
        $crawler = $this->clientFrom->request('GET', '/contact');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open contact page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on contact page!");
        }

        // get user
        $user = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user@gmail.com');

        //get form
        $form = $crawler->selectButton('Send')->form(array(
            'contact_type[name]' => $user->getFirstName(),
            'contact_type[email]' => $user->getEmail(),
            'contact_type[subject]' => 'subject',
            'contact_type[userName]' => $user->getUserName(),
            'contact_type[message]' => 'message',
            'contact_type[device]' => "IOS",
        ));

        //submit form
        $this->clientFrom->submit($form);

        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not submit contact form!");
    }

    /**
     * This function is used to check single member page
     */
    public function testSingleMember()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user222@gmail.com');

        // try to open single member page
        $this->clientFrom->request('GET', '/member/' . $userTo->getUId());

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open single member page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(13, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on single membert page!");
        }

// visitor
        // get user relation
        $userRelation = $this->em->getRepository('LBUserBundle:UserRelation')->findOneByToUser($userTo->getId());

        $this->assertEquals($userRelation->getFromVisitorStatus(), self::NEW_VISITOR, "wrong from_visitor_status from toUser in single member page!");

        $this->assertEquals($userRelation->getToVisitorStatus(), self::NATIVE, "wrong to_visitor_status from toUser in single member page!");

        // try to open visitor page
        $this->clientTo->request('GET', '/visitor');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not open visitor page!");

        // get userFrom
        $userFrom = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user@gmail.com');

        $this->assertContains($userFrom->getFirstName(), $this->clientTo->getResponse()->getContent(), "can not find userFrom's firstName in visitor page!");

// favorite
        // get base url
        $baseUrl = $this->clientFrom->getRequest()->getSchemeAndHttpHost();

        // try to favorite userTo
        $this->clientFrom->request('PUT', $baseUrl .'/api/v1.0/userrelations/' . $userTo->getId() . '/favorites/' . self::FAVORITE);

        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), self::HTTP_STATUS_NO_CONTENT, "can not favorite userTo in single member page!");

        // try to open favorite page
        $this->clientTo->request('GET', '/favorite');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not open favorite page!");

        // get userFrom
        $userFrom = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user@gmail.com');

        $this->assertContains($userFrom->getFirstName(), $this->clientTo->getResponse()->getContent(), "can not find userFrom's firstName in favorite page!");

// like userTo
        // try to like userTo
        $this->clientFrom->request('GET', $baseUrl .'/api/v1.0/users/' . $userTo->getId() . '/statuses/' . self::LIKE);

        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "can not like userTo in single member page!");

        // try to open like page
        $this->clientTo->request('GET', '/like');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not open like page!");

        // get userFrom
        $userFrom = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user@gmail.com');

        $this->assertContains($userFrom->getFirstName(), $this->clientTo->getResponse()->getContent(), "can not find userFrom's firstName in like page!");

// connections
// like userFrom
        // try to like userFrom
        $this->clientTo->request('GET', $baseUrl .'/api/v1.0/users/' . $userFrom->getId() . '/statuses/' . self::LIKE);

        $this->assertEquals($this->clientTo->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "can not like userFrom in single member page!");

        // try to open connections page
        $this->clientFrom->request('GET', '/connections');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open connections page!");

        // get userTo
//        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('user222@gmail.com');
//
//        $this->assertContains($userTo->getFirstName(), $this->clientFrom->getResponse()->getContent(), "can not find userTo's firstName in connections page!");

////////
        // try to open my page
        $this->clientTo->request('GET', '/me');

        $this->assertContains($userFrom->getFirstName(), $this->clientTo->getResponse()->getContent(), "can not find friend userFrom's firstName in my page!");
    }

    /**
     * This function is used to check nextUser action
     */
    public function testNextUser()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/next-user/%s/%s', $userTo->getId(), UserRelation::NATIVE);

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), Response::HTTP_FOUND,  "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check search action
     */
    public function testSearch()
    {
        $url = sprintf('/search');

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check likeByMe action
     */
    public function testLikeByMe()
    {
        $url = '/like-by-me';

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check favoriteByMe action
     */
    public function testFavoriteByMeAction()
    {
        $url = '/favorite-by-me';

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check removeImageAction action
     */
    public function testRemoveImage()
    {
        $url = sprintf('/remove-image/%s/%s', 'photo.jpg', 'AppBundle:Blog');

        // try to open page
        $this->adminUser->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertEquals($this->adminUser->getResponse()->getStatusCode(), Response::HTTP_FOUND, "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->adminUser->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}
