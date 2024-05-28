<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 3:44 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;

class AdControllerTest extends BaseClass
{
    /**
     * This function is used to check Ad cgetAction in rest
     */
    public function testGet()
    {
        $url = '/api/v1.0/ads';

        // try to create new Report
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get Ad in cgetAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertArrayHasKey('download_link_for_mobile', $content, 'Invalid download_link_for_mobile key in Ad cgetAction rest json structure');
        $this->assertArrayHasKey('name', $content, 'Invalid name key in Ad cgetAction rest json structure');
        $this->assertArrayHasKey('description', $content, 'Invalid description key in Ad cgetAction rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check Ad getGeoAction in rest
     */
    public function testGetGeo()
    {
        //get adGeo in database by name
        $adGeo = $this->em->getRepository('AppBundle:AdGeo')->findOneBy(array('name' => 'Test GEO Ad'));

        $url = sprintf('/api/v1.0/ads/%s/geo', $adGeo->getId());

        // try to create new Report
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get Ad in getGeoAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('download_link_for_mobile', $contents, 'Invalid download_link_for_mobile key in Ad getGeoAction rest json structure');
        $this->assertArrayHasKey('name', $contents, 'Invalid name key in Ad getGeoAction rest json structure');
        $this->assertArrayHasKey('description', $contents, 'Invalid description key in Ad getGeoAction rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}