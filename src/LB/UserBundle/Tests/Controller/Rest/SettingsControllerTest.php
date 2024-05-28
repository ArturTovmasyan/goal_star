<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 11:53 AM
 */
namespace AppBundle\Tests\Controller\Rest;

use LB\UserBundle\Tests\Controller\BaseClass;

class SettingsControllerTest extends BaseClass
{

    /**
     * This function is used to check PostEmail function in rest
     */
    public function testPostEmail()
    {
        $url = '/api/v1.0/settings/emails';

        // try to post user email settings
        $this->clientChange->request('POST', $url,
            array(
                'emailSettings' => json_encode(array("acceptFriendshipRequest" => true,
                                                    "groupInfoUpdate" => true,
                                                    "joinGroup" => true,
                                                    "newMessage" => true,
                                                    "requestJoinAdminGroup" => true,
                                                    "sendFriendshipRequest" => true,
                                                    "promotedAdminOrModerGroup" => true)),
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not post user email settings in postEmailAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetEmail function in rest
     *
     * @depends testPostEmail
     */
    public function testGetEmail()
    {
        $url = '/api/v1.0/settings/email';

        // try to get user email settings
        $this->clientChange->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not get user email settings in getEmailAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientChange->getResponse()->getContent(), true);

        $this->assertArrayHasKey('acceptFriendshipRequest', $contents, 'Invalid acceptFriendshipRequest key in settings getEmailAction rest json structure');
        $this->assertArrayHasKey('groupInfoUpdate', $contents, 'Invalid groupInfoUpdate key in settings getEmailAction rest json structure');
        $this->assertArrayHasKey('joinGroup', $contents, 'Invalid joinGroup key in settings getEmailAction rest json structure');
        $this->assertArrayHasKey('newMessage', $contents, 'Invalid newMessage key in settings getEmailAction rest json structure');
        $this->assertArrayHasKey('requestJoinAdminGroup', $contents, 'Invalid requestJoinAdminGroup key in settings getEmailAction rest json structure');
        $this->assertArrayHasKey('sendFriendshipRequest', $contents, 'Invalid sendFriendshipRequest key in settings getEmailAction rest json structure');
        $this->assertArrayHasKey('promotedAdminOrModerGroup', $contents, 'Invalid promotedAdminOrModerGroup key in settings getEmailAction rest json structure');

    }

    /**
     * This function is used to check GetProfileVisibility function in rest
     *
     * @depends testPostEmail
     */
    public function testGetProfileVisibility()
    {
        $url = '/api/v1.0/settings/profile/visibility';

        // try to get user profile visibility
        $this->clientChange->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not get user profile visibility in getProfileVisibilityAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientChange->getResponse()->getContent(), true);

        $this->assertArrayHasKey('state_visibility', $contents, 'Invalid state_visibility key in settings getProfileVisibilityAction rest json structure');
        $this->assertArrayHasKey('zip_code_visibility', $contents, 'Invalid zip_code_visibility key in settings getProfileVisibilityAction rest json structure');
        $this->assertArrayHasKey('craziest_outdoor_adventure_visibility', $contents, 'Invalid craziest_outdoor_adventure_visibility key in settings getProfileVisibilityAction rest json structure');
        $this->assertArrayHasKey('favorite_outdoor_activity_visibility', $contents, 'Invalid favorite_outdoor_activity_visibility key in settings getProfileVisibilityAction rest json structure');
        $this->assertArrayHasKey('like_try_tomorrow_visibility', $contents, 'Invalid like_try_tomorrow_visibility key in settings getProfileVisibilityAction rest json structure');
        $this->assertArrayHasKey('last_name', $contents, 'Invalid last_name key in settings getProfileVisibilityAction rest json structure');
        $this->assertEmpty($contents['last_name'], 'last_name value is not empty in getProfileVisibilityAction rest json structure');

    }

    /**
     * This function is used to check PostProfileVisibility function in rest
     *
     * @depends testGetProfileVisibility
     */
    public function testPostProfileVisibility()
    {
        $url = '/api/v1.0/settings/profiles/visibilities';

        // try to update profile visibility settings
        $this->clientChange->request('POST', $url,
            array(
                'last_name_visibility' => 3,
                'state_visibility' => 3,
                'zip_code_visibility' => 3,
                'country_visibility' => 3,
                'craziest_outdoor_adventure_visibility' => 3,
                'favorite_outdoor_activity_visibility' => 3,
                'like_try_tomorrow_visibility' => 3,
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not update profile visibility settings in postProfileVisibilityAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }



    /**
     * This function is used to check ForgotPassword function in rest
     *
     * @depends testPostProfileVisibility
     */
    public function testForgotPassword()
    {
        // get userChange
        $userChange = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('changePassword@gmail.com');

        $url = '/api/v1.0/settings/forgots/passwords';

        // try to change user password
        $this->clientChange->request('POST', $url,
            array(
                'forgotPassword' => $userChange->getUserName(),
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not change user password in postForgotPasswordAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check PostDisable function in rest
     *
     * @depends testForgotPassword
     */
    public function testPostDisable()
    {
        $url = '/api/v1.0/settings/disables';

        // try to disable user account
        $this->clientChange->request('POST', $url,
            array(
                'disabled' => true,
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not disable user account in postDisableAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientChange->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $contents, 'Invalid id key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('age', $contents, 'Invalid age key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('profile_image_path_for_mobile', $contents, 'Invalid profile_image_path_for_mobile key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('only_city', $contents, 'Invalid only_city key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('first_name', $contents, 'Invalid first_name key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('city', $contents, 'Invalid city key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('search_visibility', $contents, 'Invalid search_visibility key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('_i_am', $contents, 'Invalid _i_am key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('notification_switch', $contents, 'Invalid notification_switch key in settings postDisableAction rest json structure');
        $this->assertArrayHasKey('last_name', $contents, 'Invalid last_name key in settings postDisableAction rest json structure');
        $this->assertEmpty($contents['last_name'], 'last_name value is not empty in postDisableAction rest json structure');

        if(array_key_exists('activity', $contents)) {

            //get activity in array
            $activity = $contents['activity'];

            $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in settings postDisableAction rest json structure');
            $this->assertArrayHasKey('title', $activity, 'Invalid title key in settings postDisableAction rest json structure');
        }

        if(array_key_exists('permissions', $contents)) {

            //get permission in array
            $permission = $contents['permissions'];

            $this->assertArrayHasKey('luvbyrd_unlimited', $permission, 'Invalid luvbyrd_unlimited key in settings postDisableAction rest json structure');
        }

    }

    /**
     * This function is used to check PostGeneral function in rest
     *
     * @depends testPostDisable
     */
    public function testPostGeneral()
    {
        // get userChange
        $userChange = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('changePassword@gmail.com');

        // enable user
        $userChange->setEnabled(true);
        $this->em->persist($userChange);
        $this->em->flush();

        $url = '/api/v1.0/settings/generals';

        // try to change general setting
        $this->clientChange->request('POST', $url,
            array(
                'currentPassword' => 'superAdmin',
                'changePassword' => 'test',

            ));

        // Check that the profiler is enabled
        if ($profile = $this->clientChange->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientChange->getResponse()->isSuccessful(), "can not change general setting in postGeneralAction rest!");
    }
}