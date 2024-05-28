<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 11:53 AM
 */
namespace AppBundle\Tests\Controller\Rest;

use LB\UserBundle\Entity\UserRelation;
use LB\UserBundle\Tests\Controller\BaseClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserControllerTest extends BaseClass
{
    /**
     * This function is used to check GetActionCount function in rest
     */
    public function testGetActionCount()
    {
        $url = '/api/v1.0/user/action/count';

        // try to get User`s that related to current user
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get User`s that related to current user in getActionCountAction rest!");

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

        $this->assertArrayHasKey('like_count', $contents, 'Invalid like_count key in settings getActionCountAction rest json structure');
        $this->assertArrayHasKey('visitor_count', $contents, 'Invalid visitor_count key in settings getActionCountAction rest json structure');
        $this->assertArrayHasKey('favorite_count', $contents, 'Invalid favorite_count key in settings getActionCountAction rest json structure');
        $this->assertArrayHasKey('messages_count', $contents, 'Invalid messages_count key in settings getActionCountAction rest json structure');
        $this->assertArrayHasKey('friends_count', $contents, 'Invalid friends_count key in settings getActionCountAction rest json structure');
    }

    /**
     * This function is used to check PostSearch function in rest
     *
     */
    public function testPostSearch()
    {
        $url = '/api/v1.0/users/searches';

        // try to get user by search data
        $this->clientFrom->request('POST', $url,
            array(
                'age' => array(24, 27),
                'lookingFor' => 5,
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get user by search data in postSearchAction rest!");

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

        $content = reset($contents);

        //get image cache path in array
        $imageCachePath = $content['image_cache_path'];

        //parse url for get path
        $path = parse_url($imageCachePath);

        //substring image cache path
        $imageCachePath = $path['path'];

        //set image cache path
        $imagePath = __DIR__.'/../../../../../../web'.$imageCachePath;

        //get image size
        $imageSize = getimagesize($imagePath);

        $imageWidth = $imageSize[0];
        $imageHeight = $imageSize[1];

        $this->assertGreaterThan($imageWidth, 161, 'Invalid image size in postSearchAction rest json structure');
        $this->assertGreaterThan($imageHeight, 161, 'Invalid image size in postSearchAction rest json structure');

        //get image cache path for mobile in array
        $imageCachePathForMobile = $content['profile_image_path_for_mobile'];

        //parse url for get path
        $mobilePath = parse_url($imageCachePathForMobile);

        //substring image cache path
        $imageCachePathForMobile = $mobilePath['path'];

        //set image cache path
        $imagePathForMobile = __DIR__.'/../../../../../../web'.$imageCachePathForMobile;

        //get image size
        $imageSizeForMobile = getimagesize($imagePathForMobile);

        $imageWidth = $imageSizeForMobile[0];
        $imageHeight = $imageSizeForMobile[1];

        $this->assertGreaterThan($imageWidth, 101, 'Invalid image size in postSearchAction rest json structure');
        $this->assertGreaterThan($imageHeight, 101, 'Invalid image size in postSearchAction rest json structure');

        $this->assertArrayHasKey('id', $content, 'Invalid id key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('age', $content, 'Invalid age key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('profile_image_path', $content, 'Invalid profile_image_path key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('profile_image_path_for_mobile', $content, 'Invalid profile_image_path_for_mobile key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('show_name', $content, 'Invalid profile_image_path_for_mobile key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('only_city', $content, 'Invalid only_city key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('first_name', $content, 'Invalid first_name key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('city', $content, 'Invalid city key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('_i_am', $content, 'Invalid _i_am key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('image_cache_path', $content, 'Invalid image_cache_path key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('users_count', $content, 'Invalid users_count key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('full_name', $content, 'Invalid full_name key in user postSearchAction rest json structure');
        $this->assertArrayHasKey('last_name', $content, 'Invalid last_name key in user postSearchAction rest json structure');
        $this->assertEmpty($content['last_name'], 'last_name value is not empty in postSearchAction rest json structure');
        $this->assertEquals($content['full_name'], $content['first_name'], 'show_name and  first_name is not equals in postSearchAction rest json structure');

        if(array_key_exists('activity', $content)) {
            //get activity data in contents array
            $activity = $content['activity'];
            $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in user postSearchAction rest json structure');
            $this->assertArrayHasKey('title', $activity, 'Invalid title key in user postSearchAction rest json structure');
        }

        if(array_key_exists('all_files', $content)) {
            //get allFile data in content array
            $allFiles = $content['all_files'];
            $allFile = reset($allFiles);

            $this->assertArrayHasKey('web_path', $allFile, 'Invalid web_path key in user postSearchAction rest json structure');
            $this->assertArrayHasKey('type', $allFile, 'Invalid type key in user postSearchAction rest json structure');
            $this->assertArrayHasKey('thumb_path', $allFile, 'Invalid thumb_path key in user postSearchAction rest json structure');
        }

        // try to get user by search data
        $this->adminUser->request('POST', $url,
            array(
                'age' => array(24, 27),
                'lookingFor' => 5,
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->adminUser->getResponse()->isSuccessful(), "can not get user by search data in postSearchAction rest!");

        $this->assertTrue(
            $this->adminUser->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->adminUser->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->adminUser->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->adminUser->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertNotEmpty($content['last_name'], 'For admin user last_name must be not empty in user postSearchAction rest json structure');
    }

    /**
     * This function is used to check GetStatus function in rest
     *
     * @depends testGetActionCount
     */
    public function testGetStatus()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/users/%s/statuses/%s', $userTo->getId(), BaseClass::LIKE);

        // try to like userTo
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not like userTo in getStatusAction rest!");

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
     * This function is used to check Cget function in rest
     *
     * @depends testGetStatus
     */
    public function testCget()
    {
        $url = '/api/v1.0/users';

        // try to get all users
        $this->clientTo->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not get all users in cgetAction rest!");

        $this->assertTrue(
            $this->clientTo->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientTo->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientTo->getProfile()){
            // check the number of requests
            $this->assertLessThan(13, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientTo->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in user cgetAction rest json structure');
        $this->assertArrayHasKey('age', $content, 'Invalid age key in user cgetAction rest json structure');
        $this->assertArrayHasKey('profile_image_path_for_mobile', $content, 'Invalid profile_image_path_for_mobile key in user cgetAction rest json structure');
        $this->assertArrayHasKey('only_city', $content, 'Invalid only_city key in user cgetAction rest json structure');
        $this->assertArrayHasKey('first_name', $content, 'Invalid first_name key in user cgetAction rest json structure');
        $this->assertArrayHasKey('city', $content, 'Invalid city key in user cgetAction rest json structure');
        $this->assertArrayHasKey('_i_am', $content, 'Invalid _i_am key in user cgetAction rest json structure');
        $this->assertArrayHasKey('notification_switch', $content, 'Invalid notification_switch key in user cgetAction rest json structure');
        $this->assertArrayHasKey('last_name', $content, 'Invalid last_name key in user cgetAction rest json structure');
        $this->assertEmpty($content['last_name'], 'last_name value is not empty in cgetAction rest json structure');

        if(array_key_exists('activity', $content)) {
            //get activity data in array
            $activity = $content['activity'];

            $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in user cgetAction rest json structure');
            $this->assertArrayHasKey('title', $activity, 'Invalid title key in user cgetAction rest json structure');
        }

        if(array_key_exists('permissions', $content)) {
            //get permission data in array
            $permission = $content['permissions'];

            $this->assertArrayHasKey('luvbyrd_message', $permission, 'Invalid luvbyrd_message key in user cgetAction rest json structure');
        }
    }

    /**
     * This function is used to check GetSlider function in rest
     *
     * @depends testCget
     */
    public function testGetSlider()
    {
        $url = sprintf('/api/v1.0/users/%s/sliders/%s', 1, 10);

        // try to get all user for slider
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get all user for slider in getSliderAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(14, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        //get content data in array
        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('age', $content, 'Invalid age key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('profile_image_path_for_mobile', $content, 'Invalid profile_image_path_for_mobile key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('only_city', $content, 'Invalid only_city key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('first_name', $content, 'Invalid first_name key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('city', $content, 'Invalid city key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('_i_am', $content, 'Invalid _i_am key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('notification_switch', $content, 'Invalid notification_switch key in user getSliderAction rest json structure');
        $this->assertArrayHasKey('last_name', $content, 'Invalid last_name key in user getSliderAction rest json structure');
        $this->assertEmpty($content['last_name'], 'last_name value is not empty in getSliderAction rest json structure');

        if(array_key_exists('activity', $content)) {
            //get activity in array
            $activity = $content['activity'];

            $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in getAction rest json structure');
            $this->assertArrayHasKey('title', $activity, 'Invalid title key in getAction rest json structure');
        }

        if(array_key_exists('permissions', $content)) {
            //get permissions in array
            $permissions = $content['permissions'];

            $this->assertArrayHasKey('luvbyrd_unlimited', $permissions, 'Invalid luvbyrd_unlimited key in getAction rest json structure');
        }
    }

    /**
     * This function is used to check Get function in rest
     *
     * @depends testGetSlider
     */
    public function testGet()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        $url = sprintf('/api/v1.0/users/%s', $userTo->getId());

        // try to get user by id
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get user by id in getAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        if(array_key_exists('user', $contents)) {

            //get user data in array
            $user = $contents['user'];

            $this->assertArrayHasKey('id', $user, 'Invalid id key in user getAction rest json structure');
            $this->assertArrayHasKey('age', $user, 'Invalid age key in user getAction rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $user, 'Invalid profile_image_path_for_mobile key in user getAction rest json structure');
            $this->assertArrayHasKey('only_city', $user, 'Invalid only_city key in user getAction rest json structure');
            $this->assertArrayHasKey('first_name', $user, 'Invalid first_name key in user getAction rest json structure');
            $this->assertArrayHasKey('city', $user, 'Invalid city key in user getAction rest json structure');
            $this->assertArrayHasKey('summary', $user, 'Invalid summary key in user getAction rest json structure');
            $this->assertArrayHasKey('_i_am', $user, 'Invalid _i_am key in user getAction rest json structure');
            $this->assertArrayHasKey('personal_info', $user, 'Invalid personal_info key in user getAction rest json structure');
            $this->assertArrayHasKey('notification_switch', $user, 'Invalid notification_switch key in user getAction rest json structure');
            $this->assertArrayHasKey('last_name', $user, 'Invalid last_name key in user getAction rest json structure');
            $this->assertEmpty($user['last_name'], 'last_name value is not empty in getAction rest json structure');

            if(array_key_exists('search_visibility', $user)) {
                $this->assertArrayHasKey('search_visibility', $user, 'Invalid search_visibility key in user getAction rest json structure');
            }

            if(array_key_exists('interests_with_group', $user)) {
                //get interestWithGroup data in array
                $interestWithGroup = $user['interests_with_group'];

                //get interestGroup1 data in interestWithGroup array
                $InterestGroup1 = $interestWithGroup['InterestGroup1'];

                //get first group data in array
                $group = reset($InterestGroup1);
                $this->assertArrayHasKey('download_link_for_mobile', $group, 'Invalid download_link_for_mobile key in getAction rest json structure');
                $this->assertArrayHasKey('id', $group, 'Invalid id key in getAction rest json structure');
                $this->assertArrayHasKey('name', $group, 'Invalid name key in getAction rest json structure');
            }

            if(array_key_exists('activity', $user)) {
                //get activity in array
                $activity = $user['activity'];
                $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in getAction rest json structure');
                $this->assertArrayHasKey('title', $activity, 'Invalid title key in getAction rest json structure');
            }

            if(array_key_exists('files', $user)) {
                //get file in array
                $files = $user['files'];
                $path = reset($files);
                $this->assertArrayHasKey('web_path_for_mobile', $path, 'Invalid web_path_for_mobile key in getAction rest json structure');
            }

            if(array_key_exists('permissions', $user)) {
                //get permissions in array
                $permissions = $user['permissions'];
                $this->assertArrayHasKey('luvbyrd_unlimited', $permissions, 'Invalid luvbyrd_unlimited key in getAction rest json structure');
            }
        }

        if(array_key_exists('statuses', $contents)) {
            //get statuses data in array
            $statuses = $contents['statuses'];
            $this->assertArrayHasKey('block', $statuses, 'Invalid block key in getAction rest json structure');
            $this->assertArrayHasKey('like', $statuses, 'Invalid like key in getAction rest json structure');
            $this->assertArrayHasKey('favorite', $statuses, 'Invalid favorite key in getAction rest json structure');
        }
    }

    /**
     * This function is used to check GetRelated function in rest
     *
     * @depends testGet
     */
    public function testGetRelated()
    {
        $url = sprintf('/api/v1.0/users/%s/related', UserRelation::NATIVE);

        // try to get User`s that related to current user
        $this->clientTo->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not get User`s that related to current user in getRelatedAction rest!");

        $this->assertTrue(
            $this->clientTo->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientTo->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientTo->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientTo->getResponse()->getContent(), true);

        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in getRelatedAction rest json structure');
        $this->assertArrayHasKey('first_name', $content, 'Invalid first_name key in getRelatedAction rest json structure');
        $this->assertArrayHasKey('last_name', $content, 'Invalid last_name key in getRelatedAction rest json structure');
        $this->assertEmpty($content['last_name'], 'last_name value is not empty in getRelatedAction rest json structure');
    }

    /**
     * This function is used to check GetUserBy function in rest
     *
     * @depends testGetRelated
     */
    public function testGetUserBy()
    {
        $url = sprintf('/api/v1.0/users/%s/user/by', self::LIKE);

        // try to get user by action status
        $this->clientTo->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not get user by action status in user getUserByAction rest!");

        $this->assertTrue(
            $this->clientTo->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientTo->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientTo->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->clientTo->getResponse()->getContent(), true);

        //get first array in content data
        $content = reset($contents);

        $this->assertArrayHasKey('id', $content, 'Invalid id key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('age', $content, 'Invalid age key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('profile_image_path_for_mobile', $content, 'Invalid profile_image_path_for_mobile key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('first_name', $content, 'Invalid first_name key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('city', $content, 'Invalid city key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('_i_am', $content, 'Invalid _i_am key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('status_for_mobile', $content, 'Invalid status_for_mobile key in user getUserByAction rest json structure');
        $this->assertArrayHasKey('last_name', $content, 'Invalid last_name key in user getUserByAction rest json structure');
        $this->assertEmpty($content['last_name'], 'last_name value is not empty in getUserByAction rest json structure');

        if(array_key_exists('activity', $content)) {
            //get activity in array
            $activity = $content['activity'];

            $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in getAction rest json structure');
            $this->assertArrayHasKey('title', $activity, 'Invalid title key in getAction rest json structure');
        }

        if(array_key_exists('permissions', $content)) {
            //get permissions in array
            $permissions = $content['permissions'];

            $this->assertArrayHasKey('luvbyrd_unlimited', $permissions, 'Invalid luvbyrd_unlimited key in getAction rest json structure');
        }
    }

    /**
     * This function is used to check PostGeoPosition function in rest
     *
     * @depends testGetUserBy
     */
    public function testGetGeoPosition()
    {
        $url = '/api/v1.0/user/geo/position';

        // try to get to get Update user position
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get to get Update user position in postGeoPositionAction rest!");

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
     * This function is used to check PostLogin function in rest
     *
     */
    public function testPostLogin()
    {
        // get userFrom
        $userFrom = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestFrom@gmail.com');

        $url = '/api/v1.0/users/logins';

        // try to login user
        $this->clientFrom->request('POST', $url,
            array(
                'username' => $userFrom->getUsername(),
                'password' => 'superAdmin',
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not login user in postLoginAction rest!");

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

        $this->assertArrayHasKey('sessionId', $contents, 'Invalid sessionId key in user postLoginAction rest json structure');

        if(array_key_exists('userInfo', $contents)) {
            //get userInfo data in contents array
            $userInfo = $contents['userInfo'];

            $this->assertArrayHasKey('id', $userInfo, 'Invalid id key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('age', $userInfo, 'Invalid age key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $userInfo, 'Invalid profile_image_path_for_mobile key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('only_city', $userInfo, 'Invalid only_city key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('first_name', $userInfo, 'Invalid first_name key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('city', $userInfo, 'Invalid city key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('_i_am', $userInfo, 'Invalid _i_am key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('is_admin', $userInfo, 'Invalid is_admin key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('notification_switch', $userInfo, 'Invalid notification_switch key in user postLoginAction rest json structure');
            $this->assertArrayHasKey('last_name', $userInfo, 'Invalid last_name key in user postLoginAction rest json structure');
            $this->assertEmpty($userInfo['last_name'], 'last_name value is not empty in postLoginAction rest json structure');

            if(array_key_exists('permissions', $userInfo)) {
                //get permission data in userInfo array
                $permission = $userInfo['permissions'];

                $this->assertArrayHasKey('luvbyrd_message', $permission, 'Invalid luvbyrd_message key in user postLoginAction rest json structure');
            }

            if(array_key_exists('activity', $userInfo)) {
                //get activity data in userInfo array
                $activity = $userInfo['activity'];

                $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in user postLoginAction rest json structure');
                $this->assertArrayHasKey('title', $activity, 'Invalid title key in user postLoginAction rest json structure');
            }
        }

        // try to login user
        $this->clientFrom->request('POST', $url,
            array(
                'username' => $userFrom->getUsername(),
                'password' => 'superAdmin',
                'apikey' => true,
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not login user in postLoginAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('apiKey', $contents, 'Invalid sessionId key in user postLoginAction rest json structure');

    }

    /**
     * This function is used to check Post function in rest
     *
     * @depends testPostLogin
     */
    public function testPost()
    {
        // get interest1
        $interest1 = $this->em->getRepository('AppBundle:Interest')->findOneByName('interest1');

        $oldPhotoPath = __DIR__ . '/old_photo.jpg';
        $photoPath = __DIR__ . '/photo.jpg';

        // copy photo path
        copy($oldPhotoPath, $photoPath);

        // new uploaded file
        $photo = new UploadedFile(
            $photoPath,
            'photoUser.jpg',
            'image/jpeg',
            199
        );

        $url = '/api/v1.0/users';

        // try to register new user
        $this->client->request('POST', $url,
            array(
                'username' => 'Test',
                'email' => 'test@test.com',
                'plainPassword' => 'Test%1234',
                'firstName' => 'Test',
                'lastName' => 'Testyan',
                'birthday' => '01/12/1990',
                'city' => json_encode(array('location' => array('latitude' => 40.1791857, 'longitude' => 44.499102900000025), 'address' => 'Yerevan, Armenia')),
                'state' => 'YerevanMarz',
                'country' => 'Armenia',
                'zipCode' => 81106,
                'summary' => 'summaryTest',
                'lookingFor' => 5,
                'interests' => array($interest1->getId()),
            ),
            array('profile_image' => $photo
            ));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not register new user in postAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->client->getProfile()){
            // check the number of requests
            $this->assertLessThan(12, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('sessionId', $contents, 'Invalid sessionId key in user postAction rest json structure');
//        $this->assertArrayHasKey('userInfo', $contents, 'Invalid userInfo key in user postAction rest json structure');

        if(array_key_exists('userInfo', $contents)) {

            //get userInfo data in array
            $userInfo = $contents['userInfo'];

            $this->assertArrayHasKey('id', $userInfo, 'Invalid id key in user postAction rest json structure');
//            $this->assertArrayHasKey('username', $userInfo, 'Invalid username key in user postAction rest json structure');
//            $this->assertArrayHasKey('username_canonical', $userInfo, 'Invalid username_canonical key in user postAction rest json structure');
//            $this->assertArrayHasKey('email', $userInfo, 'Invalid email key in user postAction rest json structure');
//            $this->assertArrayHasKey('email_canonical', $userInfo, 'Invalid email_canonical key in user postAction rest json structure');
//            $this->assertArrayHasKey('enabled', $userInfo, 'Invalid enabled key in user postAction rest json structure');
//            $this->assertArrayHasKey('salt', $userInfo, 'Invalid salt key in user postAction rest json structure');
//            $this->assertArrayHasKey('password', $userInfo, 'Invalid password key in user postAction rest json structure');
//            $this->assertArrayHasKey('groups', $userInfo, 'Invalid groups key in user postAction rest json structure');
//            $this->assertArrayHasKey('locked', $userInfo, 'Invalid locked key in user postAction rest json structure');
//            $this->assertArrayHasKey('expired', $userInfo, 'Invalid expired key in user postAction rest json structure');
//            $this->assertArrayHasKey('roles', $userInfo, 'Invalid roles key in user postAction rest json structure');
//            $this->assertArrayHasKey('credentials_expired', $userInfo, 'Invalid credentials_expired key in user postAction rest json structure');
            $this->assertArrayHasKey('age', $userInfo, 'Invalid age key in user postAction rest json structure');
//            $this->assertArrayHasKey('profile_image_path', $userInfo, 'Invalid profile_image_path key in user postAction rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $userInfo, 'Invalid profile_image_path_for_mobile key in user postAction rest json structure');
//            $this->assertArrayHasKey('is_email', $userInfo, 'Invalid is_email key in user postAction rest json structure');
//            $this->assertArrayHasKey('show_name', $userInfo, 'Invalid show_name key in user postAction rest json structure');
            $this->assertArrayHasKey('only_city', $userInfo, 'Invalid only_city key in user postAction rest json structure');
//            $this->assertArrayHasKey('location', $userInfo, 'Invalid location key in user postAction rest json structure');

//            $this->assertContains('{"address":"Yerevan, Armenia","location":{"latitude":40.1791857,"longitude":44.4991029}}', $userInfo, 'Invalid location key in user postAction rest json structure');

            $this->assertArrayHasKey('first_name', $userInfo, 'Invalid first_name key in user postAction rest json structure');
            $this->assertArrayHasKey('last_name', $userInfo, 'Invalid last_name key in user postAction rest json structure');
//            $this->assertArrayHasKey('birthday', $userInfo, 'Invalid birthday key in user postAction rest json structure');
            $this->assertArrayHasKey('city', $userInfo, 'Invalid city key in user postAction rest json structure');
//            $this->assertArrayHasKey('city_lng', $userInfo, 'Invalid city_lng key in user postAction rest json structure');
//            $this->assertArrayHasKey('city_lat', $userInfo, 'Invalid city_lat key in user postAction rest json structure');
//            $this->assertArrayHasKey('summary', $userInfo, 'Invalid summary key in user postAction rest json structure');
//            $this->assertArrayHasKey('status_for_mobile', $userInfo, 'Invalid status_for_mobile key in user postAction rest json structure');
//            $this->assertArrayHasKey('is_admin', $userInfo, 'Invalid is_admin key in user postAction rest json structure');
//            $this->assertArrayHasKey('register', $userInfo, 'Invalid register key in user postAction rest json structure');
//            $this->assertArrayHasKey('i_agree', $userInfo, 'Invalid i_agree key in user postAction rest json structure');
//            $this->assertArrayHasKey('notification_switch', $userInfo, 'Invalid notification_switch key in user postAction rest json structure');
//            $this->assertArrayHasKey('created_at', $userInfo, 'Invalid created_at key in user postAction rest json structure');
//            $this->assertArrayHasKey('has_simulate_period', $userInfo, 'Invalid has_simulate_period key in user postAction rest json structure');
//            $this->assertArrayHasKey('message_image', $userInfo, 'Invalid message_image key in user postAction rest json structure');
//            $this->assertEmpty($userInfo['last_name'], 'last_name value is not empty in postAction rest json structure');
//
//            if(array_key_exists('looking_for', $userInfo)) {
//                //get looking for data in array
//                $lookingFor = $userInfo['looking_for'];
//
//                $this->assertArrayHasKey('0', $lookingFor, 'Invalid message_image key in user postAction rest json structure');
//            }

//            if(array_key_exists('profile_image', $userInfo)) {
//                //get profile image data
//                $profileImage = $userInfo['profile_image'];
//
//                $this->assertArrayHasKey('web_path', $profileImage, 'Invalid web_path key in user postAction rest json structure');
//                $this->assertArrayHasKey('web_path_for_mobile', $profileImage, 'Invalid web_path_for_mobile key in user postAction rest json structure');
//                $this->assertArrayHasKey('id', $profileImage, 'Invalid id key in user postAction rest json structure');
//                $this->assertArrayHasKey('client_name', $profileImage, 'Invalid client_name key in user postAction rest json structure');
//                $this->assertArrayHasKey('name', $profileImage, 'Invalid name key in user postAction rest json structure');
//                $this->assertArrayHasKey('size', $profileImage, 'Invalid size key in user postAction rest json structure');
//                $this->assertArrayHasKey('path', $profileImage, 'Invalid path key in user postAction rest json structure');
//                $this->assertArrayHasKey('type', $profileImage, 'Invalid type key in user postAction rest json structure');
//                $this->assertArrayHasKey('user', $profileImage, 'Invalid user key in user postAction rest json structure');
//            }

//            if(array_key_exists('interests', $userInfo)) {
//                //get interest in array data
//                $interests = $userInfo['interests'];
//
//                //get interest
//                $interest = reset($interests);
//
//                $this->assertArrayHasKey('download_link_for_mobile', $interest, 'Invalid download_link_for_mobile key in user postAction rest json structure');
//                $this->assertArrayHasKey('download_link', $interest, 'Invalid download_link key in user postAction rest json structure');
//                $this->assertArrayHasKey('id', $interest, 'Invalid id key in user postAction rest json structure');
//                $this->assertArrayHasKey('name', $interest, 'Invalid name key in user postAction rest json structure');
//                $this->assertArrayHasKey('position', $interest, 'Invalid position key in user postAction rest json structure');
//                $this->assertArrayHasKey('checked', $interest, 'Invalid checked key in user postAction rest json structure');
//
//                if(array_key_exists('group', $interest)) {
//                    //get group data in interest array
//                    $group = $interest['group'];
//
//                    $this->assertArrayHasKey('id', $group, 'Invalid id key in user postAction rest json structure');
//                    $this->assertArrayHasKey('name', $group, 'Invalid name key in user postAction rest json structure');
//                    $this->assertArrayHasKey('position', $group, 'Invalid position key in user postAction rest json structure');
//
//                    if(array_key_exists('interest', $group)) {
//                        //get interest data in group array
//                        $interestInGroups = $group['interest'];
//
//                        $interestInGroup = reset($interestInGroups);
//
//                        $this->assertArrayHasKey('download_link_for_mobile', $interestInGroup, 'Invalid download_link_for_mobile key in user postAction rest json structure');
//                        $this->assertArrayHasKey('download_link', $interestInGroup, 'Invalid download_link key in user postAction rest json structure');
//                        $this->assertArrayHasKey('id', $interestInGroup, 'Invalid id key in user postAction rest json structure');
//                        $this->assertArrayHasKey('name', $interestInGroup, 'Invalid name key in user postAction rest json structure');
//                        $this->assertArrayHasKey('position', $interestInGroup, 'Invalid position key in user postAction rest json structure');
//                        $this->assertArrayHasKey('checked', $interestInGroup, 'Invalid checked key in user postAction rest json structure');
//                    }
//                }
//            }

//            if(array_key_exists('_g_e_n_d_e_r__c_h_o_i_c_e', $userInfo)) {
//                //get genderChoise data in userInfo array
//                $genderChoice = $userInfo['_g_e_n_d_e_r__c_h_o_i_c_e'];
//
//                $this->assertContains('Man', $genderChoice, 'Invalid Man key in user postAction rest json structure');
//                $this->assertContains('Woman', $genderChoice, 'Invalid Woman key in user postAction rest json structure');
//                $this->assertContains('Bisexual', $genderChoice, 'Invalid Bisexual key in user postAction rest json structure');
//            }

            if(array_key_exists('permissions', $userInfo)) {
                //get permission data in userInfo array
                $permission = $userInfo['permissions'];

                $this->assertArrayHasKey('luvbyrd_message', $permission, 'Invalid luvbyrd_message key in user postAction rest json structure');
            }

//            if(array_key_exists('files', $userInfo)) {
//                //get files data in userInfo array
//                $files = $userInfo['files'];
//
//                //get file
//                $file = reset($files);
//
//                $this->assertArrayHasKey('web_path', $file, 'Invalid web_path key in user postAction rest json structure');
//                $this->assertArrayHasKey('web_path_for_mobile', $file, 'Invalid web_path_for_mobile key in user postAction rest json structure');
//                $this->assertArrayHasKey('id', $file, 'Invalid id key in user postAction rest json structure');
//                $this->assertArrayHasKey('client_name', $file, 'Invalid client_name key in user postAction rest json structure');
//                $this->assertArrayHasKey('name', $file, 'Invalid name key in user postAction rest json structure');
//                $this->assertArrayHasKey('size', $file, 'Invalid size key in user postAction rest json structure');
//                $this->assertArrayHasKey('path', $file, 'Invalid path key in user postAction rest json structure');
//                $this->assertArrayHasKey('type', $file, 'Invalid type key in user postAction rest json structure');
//                $this->assertArrayHasKey('user', $file, 'Invalid user key in user postAction rest json structure');
//            }

//            if(array_key_exists('gallery', $userInfo)) {
//                //get gallery data in userInfo array
//                $gallery = $userInfo['gallery'];
//
//                $this->assertArrayHasKey('0', $gallery, 'Invalid 0 key in array gallery in user postAction rest json structure');
//            }

            if(array_key_exists('activity', $userInfo)) {
                //get activity data in userInfo array
                $activity = $userInfo['activity'];

                $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in user postAction rest json structure');
            }

//            if(array_key_exists('interests_with_group', $userInfo)) {
//                //get interestWithGroup data in userInfo array
//                $interestWithGroup = $userInfo['interests_with_group'];
//
//                if($interestWithGroup) {
//
//                    //get interestGroup1 data
//                    $interestGroup1 = $interestWithGroup['InterestGroup1'];
//
//                    //get interestGroup data in $interestGroup1 array
//                    $interestGroup = reset($interestGroup1);
//
//                    $this->assertArrayHasKey('download_link_for_mobile', $interestGroup, 'Invalid download_link_for_mobile key in user postAction rest json structure');
//                    $this->assertArrayHasKey('download_link', $interestGroup, 'Invalid download_link key in user postAction rest json structure');
//                    $this->assertArrayHasKey('id', $interestGroup, 'Invalid id key in user postAction rest json structure');
//                    $this->assertArrayHasKey('name', $interestGroup, 'Invalid name key in user postAction rest json structure');
//                    $this->assertArrayHasKey('position', $interestGroup, 'Invalid position key in user postAction rest json structure');
//                    $this->assertArrayHasKey('checked', $interestGroup, 'Invalid checked key in user postAction rest json structure');
//
//                    if(array_key_exists('group', $interestGroup)) {
//                        $groupInInterest = $interestGroup['group'];
//
//                        $this->assertArrayHasKey('id', $groupInInterest, 'Invalid id key in user postAction rest json structure');
//                        $this->assertArrayHasKey('name', $groupInInterest, 'Invalid name key in user postAction rest json structure');
//                        $this->assertArrayHasKey('position', $groupInInterest, 'Invalid position key in user postAction rest json structure');
//
//                        if(array_key_exists('interest', $groupInInterest)) {
//
//                            //get interest data in $groupInInterest array
//                            $intInGroups = $groupInInterest['interest'];
//
//                            $intInGroup = reset($intInGroups);
//
//                            $this->assertArrayHasKey('download_link_for_mobile', $intInGroup, 'Invalid download_link_for_mobile key in user postAction rest json structure');
//                            $this->assertArrayHasKey('download_link', $intInGroup, 'Invalid download_link key in user postAction rest json structure');
//                            $this->assertArrayHasKey('id', $intInGroup, 'Invalid id key in user postAction rest json structure');
//                            $this->assertArrayHasKey('name', $intInGroup, 'Invalid name key in user postAction rest json structure');
//                            $this->assertArrayHasKey('position', $intInGroup, 'Invalid position key in user postAction rest json structure');
//                            $this->assertArrayHasKey('checked', $intInGroup, 'Invalid checked key in user postAction rest json structure');
//                        }
//                    }
//                }
//            }
        }

    }

    /**
     * This function is used to check getAppVersionAction rest
     */
    public function testGetAppVersion()
    {
        $url = sprintf('/api/v1.0/apps/%s/version', 'ios');

        // try to get interests ordered by positions
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get app version in getAppVersionAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $responseResults = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('mandatory', $responseResults, 'Invalid mandatory key in get app version rest json structure');
        $this->assertArrayHasKey('optional', $responseResults, 'Invalid optional key in get app version rest json structure');
    }

    /**
     * This function is used to check getAppVersionAction rest
     */
    public function testGetUserInfo()
    {
        $url = sprintf('/api/v1.0/user/info');

        // try to get interests ordered by positions
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get user info in user getInfoAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $contents, 'Invalid id key in getInfoAction rest json structure');
        $this->assertArrayHasKey('age', $contents, 'Invalid age key in getInfoAction rest json structure');
        $this->assertArrayHasKey('profile_image_path_for_mobile', $contents, 'Invalid profile_image_path_for_mobile key in getInfoAction rest json structure');
        $this->assertArrayHasKey('only_city', $contents, 'Invalid only_city key in getInfoAction rest json structure');
        $this->assertArrayHasKey('first_name', $contents, 'Invalid first_name key in getInfoAction rest json structure');
        $this->assertArrayHasKey('city', $contents, 'Invalid city key in getInfoAction rest json structure');
        $this->assertArrayHasKey('summary', $contents, 'Invalid summary key in getInfoAction rest json structure');
        $this->assertArrayHasKey('_i_am', $contents, 'Invalid _i_am key in getInfoAction rest json structure');
        $this->assertArrayHasKey('notification_switch', $contents, 'Invalid notification_switch key in getInfoAction rest json structure');
        $this->assertArrayHasKey('last_name', $contents, 'Invalid last_name key in getInfoAction rest json structure');
        $this->assertEmpty($contents['last_name'], 'last_name value is not empty in getInfoAction rest json structure');

        if(array_key_exists('personal_info', $contents)) {
            $this->assertArrayHasKey('personal_info', $contents, 'Invalid personal_info key in getInfoAction rest json structure');
        }

        if(array_key_exists('interests_with_group', $contents)) {
            //get interestWithGroup data in array
            $interestWithGroup = $contents['interests_with_group'];

            if(array_key_exists('InterestGroup1', $interestWithGroup)) {
                //get interestGroup1 data in interestWithGroup array
                $InterestGroup1 = $interestWithGroup['InterestGroup1'];

                //get first group data in array
                $group = reset($InterestGroup1);

                $this->assertArrayHasKey('download_link_for_mobile', $group, 'Invalid download_link_for_mobile key in getInfoAction rest json structure');
                $this->assertArrayHasKey('id', $group, 'Invalid id key in getInfoAction rest json structure');
                $this->assertArrayHasKey('name', $group, 'Invalid name key in getInfoAction rest json structure');
            }
        }

        if(array_key_exists('activity', $contents)) {
            //get activity in array
            $activity = $contents['activity'];

            $this->assertArrayHasKey('minute', $activity, 'Invalid minute key in getInfoAction rest json structure');
            $this->assertArrayHasKey('title', $activity, 'Invalid title key in getInfoAction rest json structure');
        }

        if(array_key_exists('files', $contents)) {
            //get file in array
            $files = $contents['files'];

            $path = reset($files);

            $this->assertArrayHasKey('web_path_for_mobile', $path, 'Invalid web_path_for_mobile key in getInfoAction rest json structure');
        }

        if(array_key_exists('permissions', $contents)) {
            //get permissions in array
            $permissions = $contents['permissions'];

            $this->assertArrayHasKey('luvbyrd_message', $permissions, 'Invalid luvbyrd_message key in getInfoAction rest json structure');
        }
    }

    /**
     * This function is used to check testGetUserSearch rest
     */
    public function testGetUserSearch()
    {
        $url = sprintf('/api/v1.0/user/search');

        // try to get interests ordered by positions
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get data in testGetUserSearch rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        if(array_key_exists('results', $contents)) {
            //get results in array
            $results = $contents['results'];

            foreach ($results as $result)
            {
                $this->assertArrayHasKey('id', $result, 'Invalid id key in getSearchAction rest json structure');
                $this->assertArrayHasKey('text', $result, 'Invalid text key in getSearchAction rest json structure');
            }
        }
    }

    /**
     * This function is used to check getSwiperAction function in rest
     *
     */
    public function testGetSwiper()
    {
        $url = '/api/v1.0/user/swiper';

        // try to get user by search data
        $this->clientTo->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not get slider data in getSwiperAction rest!");

        $this->assertTrue(
            $this->clientTo->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientTo->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientTo->getProfile()){
            // check the number of requests
            $this->assertLessThan(13, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientTo->getResponse()->getContent(), true);

        foreach ($contents as $content)
        {
            $this->assertArrayHasKey('id', $content, 'Invalid id key in getSearchAction rest json structure');
            $this->assertArrayHasKey('first_name', $content, 'Invalid id key in getSearchAction rest json structure');
            $this->assertArrayHasKey('last_name', $content, 'Invalid id key in getSearchAction rest json structure');
            $this->assertEmpty($content['last_name'], 'last_name must be empty in getSwiperAction rest json structure');

            if(array_key_exists('image_cache_path', $content)) {

                //get image cache path in array
                $imagePath = $content['image_cache_path'];

                //parse url for get path
                $imagePath = parse_url($imagePath);

                //get slice url path
                $imagePath = $imagePath['path'];

                //set image cache path
                $imageFullPath = __DIR__.'/../../../../../../web'.$imagePath;

                //get image size
                $imageSize = getimagesize($imageFullPath);

                $imageWidth = $imageSize[0];
                $imageHeight = $imageSize[1];

                $this->assertGreaterThan($imageWidth, 161, 'Invalid image size in postSearchAction rest json structure');
                $this->assertGreaterThan($imageHeight, 161, 'Invalid image size in postSearchAction rest json structure');
            }
        }
    }

    /**
     * This function is used to check postNotificationSwitchAction function in rest
     *
     */
    public function testPostNotificationSwitch()
    {
        $url = '/api/v1.0/users/notifications/switches';

        // try to get user by search data
        $this->clientFrom->request('POST', $url, array('switch' => true));

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not post user notification switch data in postNotificationSwitchAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get response content
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);
        $contents = array($contents);

        $this->assertContains('true', $contents, 'Invalid id key in user postNotificationSwitchAction rest json structure');
    }
}