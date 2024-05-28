<?php
//
//namespace LB\PaymentBundle\Tests\Controller;
//
//use LB\UserBundle\Tests\Controller\BaseClass;
//
//class MainControllerTest extends BaseClass
//{
//    /**
//     * This function is used to check createAgreement action
//     */
//    public function testCreateAgreement()
//    {
//        $url = sprintf('/payment/prepare_payment_agreement');
//
//        // try to open page
//        $crawler = $this->clientFrom->request('GET', $url);
//
//        // Assert that the response status code is 2xx
//        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open page!");
//
//        // Check that the profiler is enabled
//        if ($profile = $this->clientFrom->getProfile()){
//            // check the number of requests
//            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
//        }
//
//        //get form
//        $form = $crawler->selectButton('submit')->form(array(
//            'form[subscriber]' => 'UserEdit',
//        ));
//
////        $form['lb_user_basic_info[I_am]']->select(5);
//
//        //submit form
//        $this->clientProfile->submit($form);
//
//        // Assert that the response status code is 2xx
//        $this->assertTrue($this->clientProfile->getResponse()->isSuccessful(), "can not submit basic info form in profile edit page!");
//    }
//}
