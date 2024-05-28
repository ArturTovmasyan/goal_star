<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 3:44 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;

class InterestControllerTest extends BaseClass
{
    /**
     * This function is used to check Get function in rest
     */
    public function testGet()
    {
        $url = '/api/v1.0/interest';

        // try to get interests ordered by positions
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get interests ordered by positions in getAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        //get data in content
        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in interest getAction rest json structure');
        $this->assertArrayHasKey('name', $content, 'Invalid name key in interest getAction rest json structure');
        $this->assertArrayHasKey('position', $content, 'Invalid position key in interest getAction rest json structure');

        //get interest data in content array
        if(array_key_exists('interest', $content)) {

            //get interests in array
            $interests = $content['interest'];

            foreach ($interests as $key => $interest)
            {
                //set value for check json key structure
                $value = $key != $interest['id'] ? false : true;

                $this->assertTrue($value, '"jms/serializer-bundle": "1.*" version is not supported correctly custom key for array, please update the bundle to 0.13 version');

                $this->assertArrayHasKey('download_link_for_mobile', $interest, 'Invalid download_link_for_mobile key in interest getAction rest json structure');
                $this->assertArrayHasKey('download_link', $interest, 'Invalid download_link key in interest getAction rest json structure');
                $this->assertArrayHasKey('id', $interest, 'Invalid id key in interest getAction rest json structure');
                $this->assertArrayHasKey('name', $interest, 'Invalid name key in interest getAction rest json structure');
                $this->assertArrayHasKey('position', $interest, 'Invalid position key in interest getAction rest json structure');
            }
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}