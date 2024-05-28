<?php

namespace AppBundle\Tests\Controller;

class GroupControllerTest extends BaseClass
{
    /**
     * This function is used to check group list page
     */
    public function testIndex()
    {
        // try to open group list page
        $this->clientFrom->request('GET', '/group/');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group list page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
        }
    }

    /**
     * This function is used to check group notification action
     */
    public function testGetNotifications()
    {
        // try to open group list page
        $this->clientFrom->request('GET', '/group/notification');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group list page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group list page!");
        }
    }

    /**
     * This function is used to check group invited page
     */
    public function testInvited()
    {
        // try to open group invited page
        $this->clientFrom->request('GET', '/group/invite/list');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group invited page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group invited page!");
        }
    }

    /**
     * This function is used to check group joined page
     */
    public function testJoined()
    {
        // try to open group joined page
        $this->clientFrom->request('GET', '/group/join/list');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group joined page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group joined page!");
        }
    }

    /**
     * This function is used to check group hosting page
     */
    public function testHosting()
    {
        // try to open group joined page
        $this->clientFrom->request('GET', '/group/hosting/list');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group hosting page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group hosting page!");
        }
    }

    /**
     * This function is used to check group create page
     */
    public function testCreate()
    {
        // try to open group create page
        $crawler =$this->clientFrom->request('GET', '/group/create');

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group create page!");

        // create form and set data
        $form = $crawler->selectButton('app_bundle_lbgroup_type[submit]')->form(array(
            'app_bundle_lbgroup_type[name]' => 'Test Group create',
            'app_bundle_lbgroup_type[joinLimit]' => '12',
            'app_bundle_lbgroup_type[description]' => 'Test create Group',
            'app_bundle_lbgroup_type[type]' => 0,
            'location' => json_encode(
                array('location'=>
                    array('latitude'=> 40.1775987, 'longitude'=>44.46004689999995),
                    'address'=>'Armin Vegner St, Yerevan, Armenia')
                )
            )
        );

        // submit form
        $this->clientFrom->submit($form);

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group create page!");
        }
    }

    /**
     * This function is used to check group edit page
     */
    public function testEdit()
    {
        // get lbGroup
        $lbGroup = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('Test Group create');

        // try to open group edit page
        $crawler = $this->clientFrom->request('GET', '/group/create/' . $lbGroup->getSlug());

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group edit page!");

        // edit data
        $form = $crawler->selectButton('app_bundle_lbgroup_type[submit]')->form(array(
                'app_bundle_lbgroup_type[name]' => 'Test Group edit',
                'app_bundle_lbgroup_type[joinLimit]' => '12',
                'app_bundle_lbgroup_type[description]' => 'Test create Group',
                'app_bundle_lbgroup_type[type]' => 0,
                'location' => json_encode(
                    array('location'=>
                        array('latitude'=> 40.1775987, 'longitude'=>44.46004689999995),
                        'address'=>'Armin Vegner St, Yerevan, Armenia')
                )
            )
        );
        // submit form
        $this->clientFrom->submit($form);

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group edit page!");
        }
    }

    /**
     * This function is used to check group show page
     */
    public function testShow()
    {
        // get lbGroup
        $lbGroup = $this->em->getRepository('AppBundle:LBGroup')->findOneByName('LBGroup1');

        // try to open group show page
        $this->clientFrom->request('GET', '/group/view/' . $lbGroup->getSlug());

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not open group show page!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on group show page!");
        }
    }
}
