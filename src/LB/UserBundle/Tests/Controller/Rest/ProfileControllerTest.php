<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 11:53 AM
 */
namespace AppBundle\Tests\Controller\Rest;

use LB\UserBundle\Tests\Controller\BaseClass;

class ProfileControllerTest extends BaseClass
{
    /**
     * This function is used to check GetPageEdit function in rest
     */
    public function testGetPageEdit()
    {
        $url = '/api/v1.0/profile/page/edit';

        // try to get about me page data
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get about me page data in getPageEditAction rest!");

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

        //get user info array data in contents
        $userInfo = reset($contents);

        $this->assertArrayHasKey('is_email', $userInfo, 'Invalid is_email key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('only_city', $userInfo, 'Invalid only_city key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('location', $userInfo, 'Invalid location key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('first_name', $userInfo, 'Invalid first_name key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('birthday', $userInfo, 'Invalid birthday key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('city', $userInfo, 'Invalid city key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('summary', $userInfo, 'Invalid summary key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('_i_am', $userInfo, 'Invalid _i_am key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('looking_for', $userInfo, 'Invalid looking_for key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('last_name', $userInfo, 'Invalid last_name key in profile getPageEditAction rest json structure');

        if(array_key_exists('personal_info', $userInfo)) {
            $this->assertArrayHasKey('personal_info', $userInfo, 'Invalid personal_info key in profile getPageEditAction rest json structure');
        }

        //get groups data in array
        $groups = end($contents);

        //get group data
        $group = reset($groups);

        $this->assertArrayHasKey('id', $group, 'Invalid id key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('name', $group, 'Invalid name key in profile getPageEditAction rest json structure');
        $this->assertArrayHasKey('position', $group, 'Invalid position key in profile getPageEditAction rest json structure');

        if(array_key_exists('interest', $group)) {

            //get interests in array
            $interests = $group['interest'];

            //get first interest in array
            $interest = reset($interests);

            $this->assertArrayHasKey('download_link_for_mobile', $interest, 'Invalid download_link_for_mobile key in profile getPageEditAction rest json structure');
            $this->assertArrayHasKey('download_link', $interest, 'Invalid download_link key in profile getPageEditAction rest json structure');
            $this->assertArrayHasKey('id', $interest, 'Invalid id key in profile getPageEditAction rest json structure');
            $this->assertArrayHasKey('name', $interest, 'Invalid name key in profile getPageEditAction rest json structure');
            $this->assertArrayHasKey('position', $interest, 'Invalid position key in profile getPageEditAction rest json structure');
            $this->assertArrayHasKey('checked', $interest, 'Invalid checked key in profile getPageEditAction rest json structure');
        }
    }

    /**
     * This function is used to check PostPageEdit function in rest
     *
     * @depends testGetPageEdit
     */
    public function testPostPageEdit()
    {
        $url = '/api/v1.0/profiles/pages/edits';

        // try to update base data
        $this->clientFrom->request('POST', $url,
            array(
                'firstName' => 'Test',
                'lastName' => 'Testyan',
                'birthday' => '01/10/1970',
                'iam' => 4,
                'email' => 'userRestFrom@gmail.com',
                'summary' => 'summaryTest',
                'interests' => json_encode(array()),
                'lookingFor' => 5,

            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not update base data in postPageEditAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}