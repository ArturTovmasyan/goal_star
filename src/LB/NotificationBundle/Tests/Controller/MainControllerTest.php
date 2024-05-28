<?php

namespace LB\NotificationBundle\Tests\Controller;

use LB\UserBundle\Tests\Controller\BaseClass;

class MainControllerTest extends BaseClass
{
    /**
     * This function is used to check note action
     */
    public function testNote()
    {
        $url = sprintf('/notes');

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
}
