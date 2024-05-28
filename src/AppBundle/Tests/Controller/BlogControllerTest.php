<?php

namespace AppBundle\Tests\Controller;

class BlogControllerTest extends BaseClass
{
    /**
     * This function is used to check blog list page
     */
    public function testIndex()
    {
        // try to open blog list page
        $crawler = $this->clientFrom->request('GET', '/blog/');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open blog list page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on blog list page!");
        }

        // get blog12, blog13 and blog4
        $blog12 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog12');
        $blog13 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog13');
        $blog4 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog4');

        // check quantity of blogs
        $this->assertEquals(3, count($crawler->filter('a[id="blogId"]')), "quantity of blogs are wrong on blogs list page!");

//        // check quantity of tags
//        $this->assertEquals(3, count($crawler->filter('a[id="blogTagId"]')), "quantity of tags are wrong on blogs list page!");
//
//        // try to filter blogs by tag1
//        // click in tag1 link
//        $link = $crawler->filter('a[id="blogTagId"]')->eq(0)->link();
//        $crawler = $this->clientFrom->click($link);

        // Assert that the response content contains a blog12 title
        $this->assertContains($blog12->getTitle(), $this->clientFrom->getResponse()->getContent(), "can not find blog12 title on blogs list page!");

        // Assert that the response content contains a blog13 title
        $this->assertContains($blog13->getTitle(), $this->clientFrom->getResponse()->getContent(), "can not find blog13 title on blogs list page!");

        // Assert that the response content not contains a blog4 title
//        $this->assertCount(0, $crawler->filter('a[id="blogId"]:contains("' . $blog4->getTitle() . '")'), "blog4 can not have tag1 on blogs list page!");
    }

    /**
     * This function is used to check blog show page
     */
    public function testShow()
    {
        // get blog12
        $blog12 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog12');

        // try to open blog show page
        $this->clientFrom->request('GET', '/' . $blog12->getSlug() . '/');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open blog show page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on blog show page!");
        }

        // Assert that the response content contains a blog12 title
        $this->assertContains($blog12->getTitle(), $this->clientFrom->getResponse()->getContent(), "can not find blog12 title on blog show page!");
    }

    /**
     * This function is used to check related blog page
     */
    public function testGetRelated()
    {
        // get blog12
        $blog12 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog12');

        // try to open related blog page
        $crawler = $this->clientFrom->request('GET', '/related/' . $blog12->getSlug());

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open related blog page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on related blog page!");
        }

        // get blog13 and blog4
        $blog13 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog13');
        $blog4 = $this->em->getRepository('AppBundle:Blog')->findOneByTitle('Blog4');

        // Assert that the response content contains a blog13 title
        $this->assertContains($blog13->getTitle(), $this->clientFrom->getResponse()->getContent(), "can not find blog13 title on related blog page!");

        // Assert that the response content not contains a blog4 title
        $this->assertCount(0, $crawler->filter('a[id="blogId"]:contains("' . $blog4->getTitle() . '")'), "blog4 can not have relation with blog12 on related blog page!");
    }
}
