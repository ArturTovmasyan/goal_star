<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 3:44 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;

class ReportControllerTest extends BaseClass
{
    /**
     * This function is used to check Put function in rest
     */
    public function testPut()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/reports/%s', $userTo->getId());

        // try to create new Report
        $this->clientFrom->request('PUT', $url,
            array(
                'message' => 'Test report',
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not create new Report in putAction rest!");

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