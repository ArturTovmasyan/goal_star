<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/16/15
 * Time: 5:57 PM
 */

namespace LB\UserBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class MainControllerTest extends BaseClass
{
    /**
     * This function is used to check profile view page
     */
    public function testProfileView()
    {
        // try to open my profile view page
        $crawler = $this->clientProfile->request('GET', '/me');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not open my profile view page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientProfile->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on my profile view page!");
        }

        // click in profile link
        $link = $crawler->filter('a[id="profileId"]')->link();
        $crawler = $this->clientProfile->click($link);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not open my profile edit page!");

//EDIT BASIC INFO
//valid form

        //get form
        $form = $crawler->selectButton('Save')->form(array(
            'lb_user_basic_info[birthday][year]' => 1982,
            'lb_user_basic_info[birthday][month]' => 11,
            'lb_user_basic_info[birthday][day]' => 15,

        ));

        $form['lb_user_basic_info[I_am]']->select(5);
        $form['lb_user_basic_info[looking_for]']->select(4);

        //submit form
        $this->clientProfile->submit($form);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not submit basic info form in profile edit page!");

        // get user
        $user = $this->em->getRepository('LBUserBundle:User')->findOneByUsername('User111');

        $this->assertEquals('User111', $user->getFirstName(), "can not submit basic info form in profile edit page!");

//EDIT YOUR ACCOUNT
//valid form

        //get form
        $form = $crawler->filter('button:contains("Save")')->eq(1)->form(array(
            'lb_user_account[email]' => 'userEditAccount@gmail.com',
            'lb_user_account[firstName]' => 'UserEdit',
            'lb_user_account[lastName]' => 'UseryanEdit',

        ));

        //submit form
        $this->clientProfile->submit($form);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not submit your account form in profile edit page!");

        $this->em->clear();

        // get user
        $user = $this->em->getRepository('LBUserBundle:User')->findOneByUsername('User111');

        $this->assertEquals('userEditAccount@gmail.com', $user->getEmail(), "can not submit your account form in profile edit page!");

//EDIT Interests
//valid form

        // get interestGroup
        $interestGroup = $this->em->getRepository('AppBundle:InterestGroup')->findOneByName('interestGroup1');
        // get interest1
        $interest1 = $this->em->getRepository('AppBundle:Interest')->findOneByName('interest1');
        // get interest2
        $interest2 = $this->em->getRepository('AppBundle:Interest')->findOneByName('interest2');
        // get interest3
        $interest3 = $this->em->getRepository('AppBundle:Interest')->findOneByName('interest3');

        //get form
        $form = $crawler->filter('button:contains("Save")')->eq(2)->form(array(

        ));

        $form['lb_user_my_interests[interests][' . $interestGroup->getId() . '][' . $interest1->getId() . ']']->untick();
        $form['lb_user_my_interests[interests][' . $interestGroup->getId() . '][' . $interest2->getId() . ']']->untick();
        $form['lb_user_my_interests[interests][' . $interestGroup->getId() . '][' . $interest3->getId() . ']']->tick();

        //submit form
        $this->clientProfile->submit($form);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not submit interests form in profile edit page!");

        $this->assertCount(1, $user->getInterests(), "can not submit interests form in profile edit page!");

//EDIT PERSONAL INFO
//valid form

        //get form
        $form = $crawler->filter('button:contains("Save")')->eq(3)->form(array(
            'lb_user_personal_info[summary]' => 'summaryEdit',
            'lb_user_personal_info[craziestOutdoorAdventure]' => 'craziestOutdoorAdventure',
            'lb_user_personal_info[favoriteOutdoorActivity]' => 'favoriteOutdoorActivity',
            'lb_user_personal_info[likeTryTomorrow]' => 'likeTryTomorrow',
            'lb_user_personal_info[personalInfo]' => 'personalInfoEdit',

        ));

        //submit form
        $this->clientProfile->submit($form);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not submit personal info form in profile edit page!");

        $this->em->clear();

        // get user
        $user = $this->em->getRepository('LBUserBundle:User')->findOneByUsername('User111');

        $this->assertEquals('personalInfoEdit', $user->getPersonalInfo(), "can not submit personal info form in profile edit page!");
    }

    /**
     * This function is used to check index action
     */
    public function testIndex()
    {
        $url = sprintf('/profile11/%s', 'profile11');

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

    /**
     * This function is used to check blockAction
     */
    public function testBlock()
    {

        $url = sprintf('/profile/blocked');

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

    /**
     * This function is used to check disableAccountAction
     */
    public function testDisableAccount()
    {
        $url = 'profile/disable_account';

        // try to open page
        $this->clientFrom->request('POST', $url, array('disabled' => false));

        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), Response::HTTP_OK, "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check addImagesAction
     *
     */
    public function testAddImages()
    {
        $photoPath = __DIR__ . '/photo.jpg';

        $newPhotoPath = __DIR__ . '/new_photo.jpg';

        // copy photo path
        copy($photoPath, $newPhotoPath);

        // new uploaded file
        $photo = new UploadedFile(
            $newPhotoPath,
            'photo.jpg',
            'image/jpeg',
            123
        );

        $url = '/add-images';

        // try to add image
        $this->clientFrom->request('POST', $url, array(), array('file' => $photo));

        // Assert that the response status code is 2xx
        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), Response::HTTP_OK, "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        // check action
        $this->assertEquals('LB\UserBundle\Controller\MainController::addImagesAction', $this->clientFrom->getRequest()->attributes->get('_controller'),
            "wrong action in user create page!" );

        //get content in response
        $id = json_decode($this->clientFrom->getResponse()->getContent(), true);

        return $id;
    }

    /**
     * This function is used to check profileGalleryAction
     * @depends testAddImages
     * @param $id
     */
    public function testProfileGalleryFile($id)
    {
        $url = '/profile/gallery';

        // try to open page
        $this->clientFrom->request('POST', $url,  array('gallery_file' => $id), array());

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(11, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check setProfileAction
     */
    public function testSetProfile()
    {
        //get file id
        $fileId = $this->em->getRepository('LBUserBundle:File')->findOneBy(array('size' => 123))->getId();

        //set url
        $url = sprintf('/set-profile/%s', $fileId);

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), Response::HTTP_OK, "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check deleteAction
     *
     */
    public function testDelete()
    {
        //get file id
        $fileId = $this->em->getRepository('LBUserBundle:File')->findOneBy(array('size' => 123))->getId();

        $url = sprintf('/file-delete/%s', $fileId);

        // try to open page
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), Response::HTTP_OK, "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(12, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check activateDeactivateAction
     *
     */
    public function testActivateDeactivate()
    {
        //get user by email
        $userId = $this->em->getRepository('LBUserBundle:User')->findOneByEmail('disableUser@gmail.com')->getId();

        $url = sprintf('/activate-deactivate/%s', $userId);

        // try to open page
        $this->adminUser->request('POST', $url);

        // Assert that the response status code is 2xx
        $this->assertEquals($this->adminUser->getResponse()->getStatusCode(), Response::HTTP_FOUND, "can not open page!");

        // Check that the profiler is enabled
        if ($profile = $this->adminUser->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check emailSettingsAction
     */
    public function testEmailSettings()
    {
        // try to open user registration page
        $this->clientFrom->request('GET', '/profile/email_settings');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open email settings page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on registration page!");
        }

        // try to create user
        $crawler = $this->clientFrom->request('POST', '/profile/email_settings');

        // check action
        $this->assertEquals('LB\UserBundle\Controller\MainController::emailSettingsAction', $this->clientFrom->getRequest()->attributes->get('_controller'),
            "wrong action in user create page!" );

        // check crawler
        $this->assertTrue(is_object($crawler), "crawler is null for user create!");

        //get form
        $form = $crawler->selectButton('Save Changes')->form(array());

        $form['lb_email_settings_type[newMessage]']->tick();
        $form['lb_email_settings_type[sendFriendshipRequest]']->tick();
        $form['lb_email_settings_type[acceptFriendshipRequest]']->tick();
        $form['lb_email_settings_type[joinGroup]']->tick();
        $form['lb_email_settings_type[groupInfoUpdate]']->tick();
        $form['lb_email_settings_type[promotedAdminOrModerGroup]']->tick();
        $form['lb_email_settings_type[requestJoinAdminGroup]']->tick();

        //submit form
        $this->clientFrom->submit($form);

        $this->assertEquals($this->clientFrom->getResponse()->getStatusCode(), Response::HTTP_OK, "form submit with blank username in user registration!");
    }

}