<?php
/**
 * Created by PhpStorm.
 * User: artur
 * Date: 07/26/16
 * Time: 11:59 AM
 */
namespace LB\PaymentBundle\Tests\Controller\Rest;

use LB\UserBundle\Tests\Controller\BaseClass;

class PaymentControllerTest extends BaseClass
{
    /**
     * This function is used to check getSearchAction function in rest
     */
    public function testGetSearch()
    {
        $url = '/api/v1.0/payment/search?q=11';

        // try to get User`s that related to current user
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get User`s that related to current user in getSearchAction rest!");

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

        if(array_key_exists('results', $contents)) {
            //get result data in contents array
            $results = $contents['results'];

            $result = reset($results);

            $this->assertArrayHasKey('id', $result, 'Invalid id key in payment getSearchAction rest json structure');
            $this->assertArrayHasKey('text', $result, 'Invalid text key in payment getSearchAction rest json structure');
        }
    }


    /**
     * This function is used to check Cget function in rest
     *
     */
    public function testCget()
    {
        $url = '/api/v1.0/payments';

        // try to get all users
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get all subscribers in cgetAction rest!");

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

        $this->assertArrayHasKey('stripePublishKey', $contents, 'Invalid stripePublishKey key in payment cgetAction rest json structure');

        if(array_key_exists('subscribers', $contents)) {

            //get subscriber data in contents array
            $subscribers = $contents['subscribers'];

            foreach ($subscribers as $subscriber)
            {

                $this->assertArrayHasKey('id', $subscriber, 'Invalid id key in payment cgetAction rest json structure');
                $this->assertArrayHasKey('description', $subscriber, 'Invalid description key in payment cgetAction rest json structure');

                if(array_key_exists('plan_info', $subscriber)) {
                    //get planInfo data in array
                    $planInfo = $subscriber['plan_info'];

                    $this->assertArrayHasKey('name', $planInfo, 'Invalid name key in payment cgetAction rest json structure');
                    $this->assertArrayHasKey('amount', $planInfo, 'Invalid amount key in payment cgetAction rest json structure');
                    $this->assertArrayHasKey('currency', $planInfo, 'Invalid currency key in payment cgetAction rest json structure');
                    $this->assertArrayHasKey('interval', $planInfo, 'Invalid interval key in payment cgetAction rest json structure');
                    $this->assertArrayHasKey('intervalCount', $planInfo, 'Invalid intervalCount key in payment cgetAction rest json structure');

                    $this->assertLessThan(10, $planInfo['amount'], "Subscribers amount price are much more greater than 10!");
                }
            }
        }
    }

    /**
     * This function is used to check postAction function in rest
     *
     */
    public function testPostAction()
    {
        $url = '/api/v1.0/payments';

        $data = "{
                  \"created\": 1326853478,
                  \"livemode\": false,
                  \"id\": \"evt_00000000000000\",
                  \"type\": \"coupon.created\",
                  \"object\": \"event\",
                  \"request\": null,
                  \"pending_webhooks\": 1,
                  \"api_version\": \"2016-07-06\",
                  \"data\": {
                    \"object\": {
                      \"id\": \"50off_00000000000000\",
                      \"object\": \"coupon\",
                      \"amount_off\": null,
                      \"created\": 1468880474,
                      \"currency\": \"usd\",
                      \"duration\": \"once\",
                      \"duration_in_months\": null,
                      \"livemode\": false,
                      \"max_redemptions\": null,
                      \"metadata\": {},
                      \"percent_off\": 50,
                      \"redeem_by\": 1471499999,
                      \"times_redeemed\": 0,
                      \"valid\": true
                    }
                  }
                }";

        $postData = json_decode($data, true);

        // try to get all users
        $this->client->request('POST', $url, $postData);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->client->getResponse()->isSuccessful(), "can not create payment in postAction rest!");

        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->client->getResponse()->headers
        );

        // Check that the profiler is enabled
        if ($profile = $this->client->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $contents = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertContains('Success', $contents, 'Invalid response key in payment postAction rest');
    }
}