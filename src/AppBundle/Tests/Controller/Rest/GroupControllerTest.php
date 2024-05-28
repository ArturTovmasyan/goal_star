<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 3:44 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;

class GroupControllerTest extends BaseClass
{
    /**
     * This function is used to check put group rest
     */
    public function testPost()
    {
        $currentDate = new \DateTime();

        $date = $currentDate->format('Y/m/d');

        $data = array('name' => 'TEST GROUP', 'date' => $date, 'description' => 'Good Group for test', 'type' => false, 'limit' => 1);

        $url = '/api/v1.0/groups';

        //try to cretae comment for group
        $this->clientTo->request('POST', $url, $data);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not put comment in testPut rest!");

        $this->assertTrue(
            $this->clientTo->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientTo->getResponse()->headers
        );

        //get content in response
        $content = json_decode($this->clientTo->getResponse()->getContent(), true);

        $this->assertArrayHasKey('download_link', $content, 'Invalid download_link key in group testPost rest json structure');
        $this->assertArrayHasKey('download_link_for_mobile', $content, 'Invalid download_link_for_mobile key in comment testPut rest json structure');
        $this->assertArrayHasKey('id', $content, 'Invalid id key in group testPost rest json structure');
        $this->assertArrayHasKey('name', $content, 'Invalid name key in group testPost rest json structure');
        $this->assertArrayHasKey('slug', $content, 'Invalid slug key in group testPost rest json structure');
        $this->assertArrayHasKey('description', $content, 'Invalid description key in group testPost rest json structure');
        $this->assertArrayHasKey('moderators', $content, 'Invalid moderators key in group testPost rest json structure');
        $this->assertArrayHasKey('event_date', $content, 'Invalid event_date key in group testPost rest json structure');
        $this->assertArrayHasKey('join_limit', $content, 'Invalid join_limit key in group testPost rest json structure');
        $this->assertArrayHasKey('type', $content, 'Invalid type key in group testPost rest json structure');

        if(array_key_exists('author', $content)) {

            //get author in array
            $author = $content['author'];

            $this->assertArrayHasKey('id', $author, 'Invalid id key in group testPost rest json structure');
            $this->assertArrayHasKey('profile_image_path', $author, 'Invalid profile_image_path key in group testPost rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $author, 'Invalid profile_image_path_for_mobile key in group testPost rest json structure');
            $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in group testPost rest json structure');
            $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in group testPost rest json structure');
            $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in group testPost rest json structure');
            $this->assertEmpty($author['last_name'], 'last_name value is not empty in testPost rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientTo->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetTypes function in rest
     */
    public function testGetTypes()
    {
        $url = '/api/v1.0/group/types';

        // try to get LB Groups types
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get LB Groups types in getTypesAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertContains('group_list', $contents, 'Invalid group_list type value in testGetTypes rest json structure');
        $this->assertContains('group_invite_list', $contents, 'Invalid group_invite_list type value in testGetTypes rest json structure');
        $this->assertContains('group_joined_list', $contents, 'Invalid group_joined_list type value in testGetTypes rest json structure');
        $this->assertContains('group_hosting_list', $contents, 'Invalid group_hosting_list type value in testGetTypes rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetNotifications function in rest
     */
    public function testGetNotifications()
    {
        $url = '/api/v1.0/group/notifications';

        // try to get Notifications by LB Group for mobile
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get Notifications by LB Group for mobile in getNotificationsAction rest!");

        $this->assertTrue(
            $this->clientFrom->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientFrom->getResponse()->headers
        );

        //get content in response
        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $this->assertArrayHasKey('remove', $contents, 'Invalid remove key in testGetNotifications rest json structure');
        $this->assertArrayHasKey('invited', $contents, 'Invalid invited key in testGetNotifications rest json structure');
        $this->assertArrayHasKey('confirm', $contents, 'Invalid confirm key in testGetNotifications rest json structure');
        $this->assertArrayHasKey('request_to_admin', $contents, 'Invalid request_to_admin key in testGetNotifications rest json structure');
        $this->assertArrayHasKey('confirm_from_admin', $contents, 'Invalid confirm_from_admin key in testGetNotifications rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check PostAdminMembers function in rest
     */
    public function testPostAdminMembers()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $url = '/api/v1.0/groups/admins/members';

        $data = array('groupId' => $group->getId(),
            'userId' => $userTo->getId(),
            'status' => 1);

        // try to used to create Members from Author
        $this->clientFrom->request('POST', $url,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $error = '';

        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        if (isset($content->error)) {
            $code = $content->error->code;
            $message = $content->error->message . '. ' . $content->error->exception[0]->message;
            $error = 'code:' . $code . ', ' . 'message:' . $message . '. ';
        }

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not used to create Members from Author in postAdminMembersAction rest!");

        $this->assertArrayHasKey('author_status', $content, 'Invalid author_status key in testPostAdminMember rest json structure');
        $this->assertArrayHasKey('member_status', $content, 'Invalid member_status key in testPostAdminMember rest json structure');

        if(array_key_exists('member', $content)) {

            $member = $content['member'];

            $this->assertArrayHasKey('id', $member, 'Invalid id key in testPostAdminMember rest json structure');
            $this->assertArrayHasKey('profile_image_path', $member, 'Invalid profile_image_path key in testPostAdminMember rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $member, 'Invalid profile_image_path_for_mobile key in testPostAdminMember rest json structure');
            $this->assertArrayHasKey('show_name', $member, 'Invalid show_name key in testPostAdminMember rest json structure');
            $this->assertArrayHasKey('first_name', $member, 'Invalid first_name key in testPostAdminMember rest json structure');
            $this->assertArrayHasKey('last_name', $member, 'Invalid last_name key in testPostAdminMember rest json structure');
            $this->assertEmpty($member['last_name'], 'last_name value is not empty in testPostAdminMember rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(13, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check PostAdminModerator function in rest
     */
    public function testPostAdminModerator()
    {
        // get userTo
        $userTo = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $url = '/api/v1.0/groups/admins/moderators';

        $data = array('groupId' => $group->getId(),
                'userId' => $userTo->getId(),
                'status' => 1);

        // try to used to create Moderator from Author
        $this->clientFrom->request('POST', $url,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $error = '';

        if (isset($content->error)) {
            $code = $content->error->code;
            $message = $content->error->message . '. ' . $content->error->exception[0]->message;
            $error = 'code:' . $code . ', ' . 'message:' . $message . '. ';
        }

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not used to create Moderator from Author in postAdminModeratorAction rest!");

        $this->assertArrayHasKey('author_status', $content, 'Invalid author_status key in testPostAdminModerator r rest json structure');
        $this->assertArrayHasKey('moderator_status', $content, 'Invalid member_status key in testPostAdminModerator rest json structure');

        if(array_key_exists('moderator', $content)) {

            $moderator = $content['moderator'];

            $this->assertArrayHasKey('id', $moderator, 'Invalid id key in testPostAdminModerator rest json structure');
            $this->assertArrayHasKey('profile_image_path', $moderator, 'Invalid profile_image_path key in testPostAdminModerator rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $moderator, 'Invalid profile_image_path_for_mobile key in testPostAdminModerator rest json structure');
            $this->assertArrayHasKey('show_name', $moderator, 'Invalid show_name key in testPostAdminModerator rest json structure');
            $this->assertArrayHasKey('first_name', $moderator, 'Invalid first_name key in testPostAdminModerator rest json structure');
            $this->assertArrayHasKey('last_name', $moderator, 'Invalid last_name key in testPostAdminModerator rest json structure');
            $this->assertEmpty($moderator['last_name'], 'last_name value is not empty in testPostAdminModerator rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(13, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check PostMember function in rest
     */
    public function testPostMember()
    {
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $url = '/api/v1.0/groups/members';

        $data = array('group' => $group->getId(),
            'status' => 1);

        // try to join or reject Members from User
        $this->clientFrom->request('POST', $url,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $error = '';

        if (isset($content->error)) {
            $code = $content->error->code;
            $message = $content->error->message . '. ' . $content->error->exception[0]->message;
            $error = 'code:' . $code . ', ' . 'message:' . $message . '. ';
        }

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not join or reject Members from User in postMemberAction rest!");

        $this->assertArrayHasKey('author_status', $content, 'Invalid author_status key in testPostMember rest json structure');
        $this->assertArrayHasKey('member_status', $content, 'Invalid member_status key in testPostMember rest json structure');

        if(array_key_exists('member', $content)) {

            $member = $content['member'];

            $this->assertArrayHasKey('id', $member, 'Invalid id key in testPostMember rest json structure');
            $this->assertArrayHasKey('profile_image_path', $member, 'Invalid profile_image_path key in testPostMember rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $member, 'Invalid profile_image_path_for_mobile key in testPostMember rest json structure');
            $this->assertArrayHasKey('show_name', $member, 'Invalid show_name key in testPostMember rest json structure');
            $this->assertArrayHasKey('first_name', $member, 'Invalid first_name key in testPostMember rest json structure');
            $this->assertArrayHasKey('last_name', $member, 'Invalid last_name key in testPostMember rest json structure');
            $this->assertEmpty($member['last_name'], 'last_name value is not empty in testPostMember rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

    }

    /**
     * This function is used to check PostModerator function in rest
     */
    public function testPostModerator()
    {
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $url = '/api/v1.0/groups/moderators';

        $data = array('group' => $group->getId(),
            'status' => 1);

        // try to join or reject Moderator from User
        $this->clientFrom->request('POST', $url,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        $error = '';

        if (isset($content->error)) {
            $code = $content->error->code;
            $message = $content->error->message . '. ' . $content->error->exception[0]->message;
            $error = 'code:' . $code . ', ' . 'message:' . $message . '. ';
        }

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not join or reject Moderator from User in postModeratorAction rest!");


        $this->assertArrayHasKey('author_status', $content, 'Invalid author_status key in testPostModerator rest json structure');
        $this->assertArrayHasKey('moderator_status', $content, 'Invalid member_status key in testPostModerator rest json structure');

        if(array_key_exists('moderator', $content)) {

            $moderator = $content['moderator'];

            $this->assertArrayHasKey('id', $moderator, 'Invalid id key in testPostModerator rest json structure');
            $this->assertArrayHasKey('profile_image_path', $moderator, 'Invalid profile_image_path key in testPostModerator rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $moderator, 'Invalid profile_image_path_for_mobile key in testPostModerator rest json structure');
            $this->assertArrayHasKey('show_name', $moderator, 'Invalid show_name key in testPostModerator rest json structure');
            $this->assertArrayHasKey('first_name', $moderator, 'Invalid first_name key in testPostModerator rest json structure');
            $this->assertArrayHasKey('last_name', $moderator, 'Invalid last_name key in testPostModerator rest json structure');
            $this->assertEmpty($moderator['last_name'], 'last_name value is not empty in testPostModerator rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetCalendar function in rest
     */
    public function testGetCalendar()
    {
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $date = $group->getEventDate();
        $date = $date->format('Y-m-d');

        $url = '/api/v1.0/group/types';

        // try to get LB Groups types
        $this->clientFrom->request('GET', $url);

        $types = $this->clientFrom->getResponse()->getContent();

        foreach (json_decode($types) as $type) {
            $url = sprintf('/api/v1.0/groups/%s/calendars/%s', $date, $type);

            // try to get users for select2
            $this->clientFrom->request('GET', $url);

            // Assert that the response status code is 2xx
            $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get users for select2 in getCalendarAction rest!");

            //get content in response
            $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

            if(array_key_exists(0, $contents)) {

                //get data in array for checking response json structure
                $data = $contents[0];

                $this->assertArrayHasKey('download_link', $data, 'Invalid download_link key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('download_link_for_mobile', $data, 'Invalid download_link_for_mobile key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('name', $data, 'Invalid name key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('slug', $data, 'Invalid slug key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('group_count', $data, 'Invalid group_count key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('event_date', $data, 'Invalid event_date key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('type', $data, 'Invalid type key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('address', $data, 'Invalid address key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('latitude', $data, 'Invalid latitude key in testGetCalendar rest json structure');
                $this->assertArrayHasKey('longitude', $data, 'Invalid longitude key in testGetCalendar rest json structure');

                if(array_key_exists('author', $data)) {
                    //get author data in array
                    $author = $data['author'];

                    $this->assertArrayHasKey('author', $data, 'Invalid author key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('id', $author, 'Invalid id key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('profile_image_path', $author, 'Invalid profile_image_path key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('profile_image_path_for_mobile', $author, 'Invalid profile_image_path_for_mobile key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('first_name', $author, 'Invalid first_name key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('image_cache_path', $author, 'Invalid image_cache_path key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('full_name', $author, 'Invalid full_name key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in testGetCalendar rest json structure');
                    $this->assertEmpty($author['last_name'], 'last_name value is not empty in testPostModerator rest json structure');
                    $this->assertEquals($author['full_name'], $author['first_name'], 'show_name and  first_name is not equals in testGetCalendar rest json structure');
                }

                if(array_key_exists('moderators', $data)) {

                    //get author data in array
                    $moderators = $data['moderators'];

                    if(array_key_exists(0, $moderators)) {
                        $moderator = $moderators[0];

                        $this->assertArrayHasKey('author_status', $moderator, 'Invalid author_status key in testGetCalendar rest json structure');
                        $this->assertArrayHasKey('moderator_status', $moderator, 'Invalid moderator_status key in testGetCalendar rest json structure');

                        if(array_key_exists('moderator', $moderator)) {

                            //get moderator in moderators array
                            $moder = $moderator['moderator'];

                            $this->assertArrayHasKey('id', $moder, 'Invalid id key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('profile_image_path', $moder, 'Invalid profile_image_path key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('profile_image_path_for_mobile', $moder, 'Invalid profile_image_path_for_mobile key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('show_name', $moder, 'Invalid show_name key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('first_name', $moder, 'Invalid first_name key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('last_name', $moder, 'Invalid last_name key in testGetCalendar rest json structure');
                            $this->assertEmpty($moder['last_name'], 'last_name value is not empty in testGetCalendar rest json structure');
                        }
                    }
                }

                if(array_key_exists('members', $data)) {

                    //get author data in array
                    $members = $data['members'];

                    if(array_key_exists(0, $members)) {

                        $member = $members[0];

                        $this->assertArrayHasKey('author_status', $member, 'Invalid author_status key in testGetCalendar rest json structure');
                        $this->assertArrayHasKey('member_status', $member, 'Invalid member_status key in testGetCalendar rest json structure');

                        if(array_key_exists('member', $member)) {

                            $memberInArray = $member['member'];

                            $this->assertArrayHasKey('id', $memberInArray, 'Invalid id key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('profile_image_path', $memberInArray, 'Invalid profile_image_path key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('profile_image_path_for_mobile', $memberInArray, 'Invalid profile_image_path_for_mobile key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('show_name', $memberInArray, 'Invalid show_name key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('first_name', $memberInArray, 'Invalid first_name key in testGetCalendar rest json structure');
                            $this->assertArrayHasKey('last_name', $memberInArray, 'Invalid last_name key in testGetCalendar rest json structure');
                            $this->assertEmpty($memberInArray['last_name'], 'last_name value is not empty in testGetCalendar rest json structure');
                        }
                    }
                }
            }
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetMobileCalendar function in rest
     */
    public function testGetMobileCalendar()
    {
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $date = $group->getEventDate();
        $date = $date->format('Y-m-d');

        $url = '/api/v1.0/group/types';

        // try to get LB Groups types
        $this->clientFrom->request('GET', $url);

        $types = $this->clientFrom->getResponse()->getContent();

        foreach (json_decode($types) as $type) {

            $url = sprintf('/api/v1.0/groups/%s/mobiles/%s/calendar', $date, $type);

            // try to get LB Groups mobile used date and type of group
            $this->clientFrom->request('GET', $url);

            $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

            $error = '';

            if (isset($content->error)) {
                $code = $content->error->code;
                $message = $content->error->message . '. ' . $content->error->exception[0]->message;
                $error = 'code:' . $code . ', ' . 'message:' . $message . '. ';
            }

            // Assert that the response status code is 2xx
            switch ($type) {
                case 'group_list':
                    $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not get LB Groups mobile used date and type of group in getMobileCalendarAction rest!");
                    break;
                case 'group_joined_list':
                    $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not get LB Groups mobile used date and type of group in getMobileCalendarAction rest!");
                    break;
                case 'group_hosting_list':
                    $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "$error Can not get LB Groups mobile used date and type of group in getMobileCalendarAction rest!");
                    break;
            }

            if(is_array($content) && array_key_exists(0, $content)) {

                //get data in array
                $data = $content[0];

                $this->assertArrayHasKey('download_link_for_mobile', $data, 'Invalid download_link_for_mobile key in testGetMobileCalendar rest json structure');
                $this->assertArrayHasKey('name', $data, 'Invalid name key in testGetMobileCalendar rest json structure');
                $this->assertArrayHasKey('slug', $data, 'Invalid slug key in testGetMobileCalendar rest json structure');
                $this->assertArrayHasKey('event_date', $data, 'Invalid event_date key in testGetMobileCalendar rest json structure');
                $this->assertArrayHasKey('type', $data, 'Invalid type key in testGetMobileCalendar rest json structure');
                $this->assertArrayHasKey('address', $data, 'Invalid address key in testGetMobileCalendar rest json structure');

                if(array_key_exists('author', $data)) {

                    //get author data in array
                    $author = $data['author'];

                    $this->assertArrayHasKey('author', $data, 'Invalid author key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('id', $author, 'Invalid id key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('profile_image_path_for_mobile', $author, 'Invalid profile_image_path_for_mobile key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in testGetCalendar rest json structure');
                    $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in testGetCalendar rest json structure');
                    $this->assertEmpty($author['last_name'], 'last_name value is not empty in testGetCalendar rest json structure');
                }
            }
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check CgetUsers function in rest
     */
    public function testCgetUsers()
    {
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $url = sprintf('/api/v1.0/groups/%s/users/%s', $group->getId(), 'member');

        // try to get users for select2
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get users for select2 in cgetUsersAction rest!");

        //get content
        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        if(array_key_exists('items', $content)) {

            //get item in array
            $items = $content['items'];

            foreach ($items as $item) {

                $this->assertArrayHasKey('id', $item, 'Invalid id key in testCgetUsers rest json structure');
                $this->assertArrayHasKey('label', $item, 'Invalid label keyin testCgetUsers rest json structure');
            }
        }

        $this->assertArrayHasKey('more', $content, 'Invalid more key in testCgetUsers rest json structure');
        $this->assertArrayHasKey('status', $content, 'Invalid status key in testCgetUsers rest json structure');

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetSingle function in rest
     */
    public function testGetSingle()
    {
        //get group
        $group = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup2');

        $url = sprintf('/api/v1.0/groups/%s/single', $group->getSlug());

        // try to get LB Group by group slug for mobile
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get LB Group by group slug for mobile in getSingleAction rest!");

        $content = json_decode($this->clientFrom->getResponse()->getContent(), true);

        if(array_key_exists('settings', $content)) {

            //get settings data in array
            $settings = $content['settings'];

            $this->assertArrayHasKey('settings', $content, 'Invalid settings type value in testGetSingle rest json structure');
            $this->assertArrayHasKey('state', $settings, 'Invalid state type value in testGetSingle rest json structure');

            if (array_key_exists('moderators', $settings)) {
                $this->assertArrayHasKey('moderators', $settings, 'Invalid moderators type value in testGetSingle rest json structure');
            }

            if(array_key_exists('members', $settings)) {

                //get members data in array
                $members = $settings['members'];

                if(array_key_exists(0, $members)) {

                    $member = $members[0];

                    $this->assertArrayHasKey('author_status', $member, 'Invalid author_status key in testGetSingle rest json structure');
                    $this->assertArrayHasKey('member_status', $member, 'Invalid member_status key in testGetSingle rest json structure');

                    if(array_key_exists('member', $member)) {

                        $memberInArray = $member['member'];

                        $this->assertArrayHasKey('id', $memberInArray, 'Invalid id key in testGetSingle rest json structure');
                        $this->assertArrayHasKey('profile_image_path_for_mobile', $memberInArray, 'Invalid profile_image_path_for_mobile key in testGetSingle rest json structure');
                        $this->assertArrayHasKey('show_name', $memberInArray, 'Invalid show_name key in testGetSingle rest json structure');
                        $this->assertArrayHasKey('last_name', $memberInArray, 'Invalid last_name key in testGetSingle rest json structure');
                        $this->assertEmpty($memberInArray['last_name'], 'last_name value is not empty in testGetSingle rest json structure');
                    }
                }
            }
        }

        if(array_key_exists('lbGroup', $content)) {

            //get lbGroup data in content
            $lbGroup = $content['lbGroup'];

            $this->assertArrayHasKey('download_link_for_mobile', $lbGroup, 'Invalid download_link_for_mobile key in testGetSingle rest json structure');
            $this->assertArrayHasKey('id', $lbGroup, 'Invalid id key in testGetSingle rest json structure');
            $this->assertArrayHasKey('name', $lbGroup, 'Invalid name key in testGetSingle rest json structure');
            $this->assertArrayHasKey('slug', $lbGroup, 'Invalid slug key in testGetSingle rest json structure');
            $this->assertArrayHasKey('description', $lbGroup, 'Invalid description key in testGetSingle rest json structure');
            $this->assertArrayHasKey('event_date', $lbGroup, 'Invalid event_date key in testGetSingle rest json structure');
            $this->assertArrayHasKey('type', $lbGroup, 'Invalid type key in testGetSingle rest json structure');
            $this->assertArrayHasKey('address', $lbGroup, 'Invalid address key in testGetSingle rest json structure');
            $this->assertArrayHasKey('latitude', $lbGroup, 'Invalid latitude key in testGetSingle rest json structure');
            $this->assertArrayHasKey('longitude', $lbGroup, 'Invalid longitude key in testGetSingle rest json structure');

            if(array_key_exists('author', $lbGroup)) {

                //get author data in array
                $author = $lbGroup['author'];

                $this->assertArrayHasKey('author', $lbGroup, 'Invalid author key in testGetSingle rest json structure');
                $this->assertArrayHasKey('id', $author, 'Invalid id key in testGetSingle rest json structure');
                $this->assertArrayHasKey('profile_image_path_for_mobile', $author, 'Invalid profile_image_path_for_mobile key in testGetSingle rest json structure');
                $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in testGetSingle rest json structure');
                $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in testGetSingle rest json structure');

                if(array_key_exists('profile_image_path', $author)) {
                    $this->assertArrayHasKey('profile_image_path', $author, 'Invalid profile_image_path key in testGetSingle rest json structure');
                }
            }
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(14, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetMobileCalendarDates function in rest
     */
    public function testGetMobileCalendarDates()
    {
        $url = sprintf('/api/v1.0/groups/%s/mobile/calendar/dates', 'group_list');

        // try to get LB Groups dates for mobile
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get LB Groups dates for mobile in getMobileCalendarDatesAction rest!");

        $contents = json_decode($this->clientFrom->getResponse()->getContent(), true);

        foreach ($contents as $content)
        {
            $this->assertArrayHasKey('date', $content, 'Invalid date type value in testGetMobileCalendarDates rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}