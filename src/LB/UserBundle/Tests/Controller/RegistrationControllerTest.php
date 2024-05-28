<?php

namespace LB\UserBundle\Tests\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegistrationControllerTest extends BaseClass
{
    /**
     * This function is used to check sign up in luvbyrd
     */
    public function testRegistration()
    {

        $image = $this->em->getRepository('LBUserBundle:File')->findOneBy(array('clientName' => 'test_image2.jpg'));

//STEP 1
        //try to create member user
        $crawler = $this->client->request('POST', '/register/');

        //check action
        $this->assertEquals('LB\UserBundle\Controller\RegistrationController::registerAction', $this->client->getRequest()->attributes->get('_controller'),
            "wrong action in user register step 1 page!" );

        //check crawler
        $this->assertTrue(is_object($crawler), "crawler is null for user create!");

        //Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not open registration page!");

        //Check that the profiler is enabled
        if ($profile = $this->client->getProfile()) {
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on registration page!");
        }

//blank username
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => '',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
        ));

        $form['fos_user_registration_form[iAgree]']->tick();

        $this->client->submit($form);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank username in user registration!");

//blank email
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => '',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
            ));

        $form['fos_user_registration_form[iAgree]']->tick();

        $this->client->submit($form);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank email in user registration!");

//blank password
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => '',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
            ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank password in user registration!");

//invalid password confirm
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test12',
            ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank password in user registration!");

//blank firstName
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => '',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank firstname in user registration!");

//invalid minLength firstName
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'M',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid firstname in user registration!");

//invalid maxLength firstName
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Mewtewwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwwewgferge',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid firstname in user registration!");

//invalid minLength lastName
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'M',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid minLength lastName in user registration!");

//invalid maxLength lastName
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyanfdpgbkpfdokgpdfjgpfdjoidsjgodfj',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid maxLength lastName in user registration!");

//invalid agree
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'grno777',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid agree in user registration!");

//this username already registered
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'User',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with username already registered in user registration!");

//this email already registered
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'Member1111',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'user@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
             ));
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with mail already registered in user registration!");

//valid form
        $form = $crawler->selectButton('Complete')->form(array(
            'fos_user_registration_form[username]' => 'Member1',
            'fos_user_registration_form[firstName]' => 'Member1',
            'fos_user_registration_form[lastName]' => 'Memberyan1',
            'fos_user_registration_form[email]' => 'member1@gmail.com',
            'fos_user_registration_form[plainPassword][first]' => 'Test1234',
            'fos_user_registration_form[plainPassword][second]' => 'Test1234',
            'gallery_file' => json_encode([$image->getId()]),
        ));
        $form['fos_user_registration_form[I_am]']->select(4);
        $form['fos_user_registration_form[looking_for]']->select(5);
        $form['fos_user_registration_form[iAgree]']->tick();
        $this->client->submit($form);

        //follow redirect
        $this->client->followRedirect();

        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank username in user registration!");

////STEP 2
//        // try to create member user
//        $crawler = $this->client->request('GET', '/register-step-2');
//
//        // check action
//        $this->assertEquals('LB\UserBundle\Controller\RegistrationController::registerStep2Action', $this->client->getRequest()->attributes->get('_controller'),
//            "wrong action in user register step 2 page!" );
//
//        //check crawler
//        $this->assertTrue(is_object($crawler), "crawler is null for user create!");
//
//        // Assert that the response status code is 2xx
//        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not open registration 2 step page!");
//
//        // Check that the profiler is enabled
//        if ($profile = $this->client->getProfile()){
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on registration page!");
//        }
//
////blank city
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_2[birthday][year]' => 1980,
//            'lb_user_registration_2[birthday][month]' => 10,
//            'lb_user_registration_2[birthday][day]' => 10,
//            'lb_user_registration_2[city]' => '',
//            'lb_user_registration_2[location]' => ''));
//        $form['lb_user_registration_2[I_am]']->select(4);
//        $form['lb_user_registration_2[looking_for]']->select(5);
//
//        $this->client->submit($form);
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank city in user registration!");
//
////invalid minLength city
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_2[birthday][year]' => 1980,
//            'lb_user_registration_2[birthday][month]' => 10,
//            'lb_user_registration_2[birthday][day]' => 10,
//            'lb_user_registration_2[city]' => 'Ye',
//            'lb_user_registration_2[location]' => ''));
//        $form['lb_user_registration_2[I_am]']->select(4);
//        $form['lb_user_registration_2[looking_for]']->select(5);
//
//        $this->client->submit($form);
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid minLength city in user registration!");
//
////invalid maxLength city
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_2[birthday][year]' => 1980,
//            'lb_user_registration_2[birthday][month]' => 10,
//            'lb_user_registration_2[birthday][day]' => 10,
//            'lb_user_registration_2[city]' => 'YerevansdjgvodsijosdjfoivsdjofjsdofjsdjfvsdsdifsdifhYerevansdjgvodsijosdjfoivsdjofjsdofjsdjfvsdsdifsdifhYerevansdjgvodsijosdjfoivsdjofjsdofjsdjfvsdsdifsdifh',
//            'lb_user_registration_2[location]' => ''));
//        $form['lb_user_registration_2[I_am]']->select(4);
//        $form['lb_user_registration_2[looking_for]']->select(5);
//
//        $this->client->submit($form);
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid max length city in user registration!");
//
////without looking for
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_2[birthday][year]' => 1980,
//            'lb_user_registration_2[birthday][month]' => 10,
//            'lb_user_registration_2[birthday][day]' => 10,
//            'lb_user_registration_2[city]' => 'Yerevan, Armenia',
//            'lb_user_registration_2[location]' => json_encode(array('location' => array('latitude' => 40.1791857, 'longitude' => 44.499102900000025), 'address' => 'Yerevan, Armenia'))));
//        $form['lb_user_registration_2[I_am]']->select(4);
//
//        $this->client->submit($form);
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with without looking for in user registration!");
//
//    //valid form
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_2[birthday][year]' => 1980,
//            'lb_user_registration_2[birthday][month]' => 10,
//            'lb_user_registration_2[birthday][day]' => 10,
//            'lb_user_registration_2[city]' => 'Yerevan, Armenia',
//            'lb_user_registration_2[location]' => json_encode(array('location' => array('latitude' => 40.1791857, 'longitude' => 44.499102900000025), 'address' => 'Yerevan, Armenia'))));
//        $form['lb_user_registration_2[I_am]']->select(4);
//        $form['lb_user_registration_2[looking_for]']->select(5);
//
//        $this->client->submit($form);
//
//        //follow redirect
//        $this->client->followRedirect();
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank username in user registration!");
//
//        //get interestGroup
//        $interestGroup = $this->em->getRepository('AppBundle:InterestGroup')->findOneByName('interestGroup1');
//        //get interest1
//        $interest1 = $this->em->getRepository('AppBundle:Interest')->findOneByName('interest1');
//
////STEP 3
//        //try to create member user
//        $crawler = $this->client->request('GET', '/register-step-3');
//
//        //check action
//        $this->assertEquals('LB\UserBundle\Controller\RegistrationController::registerStep3Action', $this->client->getRequest()->attributes->get('_controller'),
//            "wrong action in user register step 3 page!" );
//
//        //check crawler
//        $this->assertTrue(is_object($crawler), "crawler is null for user create!");
//
//        //Assert that the response status code is 2xx
//        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not open registration 2 step page!");
//
//        //Check that the profiler is enabled
//        if ($profile = $this->client->getProfile()){
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on registration page!");
//        }
//
/////blank summary
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_3[feet]' => 5,
//            'lb_user_registration_3[inches]' => 5,
//            'lb_user_registration_3[zipCode]' => 81106,
//            'lb_user_registration_3[summary]' => ''
//        ));
//        $form['lb_user_registration_3[interests][' . $interestGroup->getId() . '][' . $interest1->getId() . ']']->tick();
//
//        $this->client->submit($form);
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank summary in user registration!");
//
////invalid minLength summary
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_3[feet]' => 5,
//            'lb_user_registration_3[inches]' => 5,
//            'lb_user_registration_3[zipCode]' => 81106,
//            'lb_user_registration_3[summary]' => 'su'
//        ));
//        $form['lb_user_registration_3[interests][' . $interestGroup->getId() . '][' . $interest1->getId() . ']']->tick();
//
//        $this->client->submit($form);
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with invalid minLength summary in user registration!");
//
////blank interest
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_3[feet]' => 5,
//            'lb_user_registration_3[inches]' => 5,
//            'lb_user_registration_3[zipCode]' => 81106,
//            'lb_user_registration_3[summary]' => 'su'
//        ));
//
//        $this->client->submit($form);
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank interest in user registration!");
//
//    //valid form
//        $form = $crawler->selectButton('continue')->form(array(
//            'lb_user_registration_3[feet]' => 5,
//            'lb_user_registration_3[inches]' => 5,
//            'lb_user_registration_3[zipCode]' => 81106,
//            'lb_user_registration_3[summary]' => 'summary'
//        ));
//
//        $form['lb_user_registration_3[interests][' . $interestGroup->getId() . '][' . $interest1->getId() . ']']->tick();
//        $this->client->submit($form);

        //follow redirect
//        $this->client->followRedirect();
//
//        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "form submit with blank username in user registration!");
    }

    /**
     * This function is used to check password resetting
     */
    public function testResetting()
    {
        //try to open login page
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "cannot open login page!");

        //click in resetting link
        $link = $crawler->selectLink('Forget your password?')->link();
        $this->client->click($link);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_OK, "can not redirect in resetting page from login page!");

        //try to open resetting page
        $crawler = $this->client->request('GET', '/resetting/request');

        //Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not open resetting page!");

        //Check that the profiler is enabled
        if ($profile = $this->client->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on resetting page!");
        }

        //get form
        $form = $crawler->selectButton('Send My Details !')->form(['username' => 'user@gmail.com']);

        //submit form
        $this->client->submit($form);

        $this->assertEquals($this->client->getResponse()->getStatusCode(), self::HTTP_STATUS_REDIRECT, "cannot reset password!");
    }
}
