<?php
/**
 * Created by PhpStorm.
 * User: artur
 * Date: 07/25/16
 * Time: 12:10 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;

class TagControllerTest extends BaseClass
{
    /**
     * This function is used to check Get tag in rest
     */
    public function testGet()
    {
        $url = '/api/v1.0/tag';

        // try to get interests ordered by positions
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get tags in getAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in esponse
        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertContains('Tag1', $content, 'Invalid group_list value in tag getAction rest json structure');
        $this->assertContains('Tag2', $content, 'Invalid group_list value in tag getAction rest json structure');
        $this->assertContains('Tag3', $content, 'Invalid group_list value in tag getAction rest json structure');
        $this->assertContains('Tag4', $content, 'Invalid group_list value in tag getAction rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}