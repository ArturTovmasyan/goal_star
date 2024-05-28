<?php

namespace LB\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseClass extends WebTestCase
{
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_REDIRECT = 302;
    const HTTP_STATUS_NO_CONTENT = 204;
    // constant for user relation status
    const LIKE     = 0;
    const FAVORITE = 1;
    const NEW_VISITOR  = 2;
    const MESSAGE  = 3;
    const FRIEND   = 4;
    const DENIED   = 5;
    const BLOCK    = 6;
    const NATIVE   = 7;
    const VISITOR  = 8;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var null
     */
    protected $client = null;

    /**
     * @var null
     */
    protected $clientFrom = null;
    /**
     * @var null
     */
    protected $clientTo = null;
    /**
     * @var null
     */
    protected $clientChange = null;
    /**
     * @var null
     */
    protected $clientProfile = null;

    /**
     * @var null
     */
    protected $user = null;

    /**
     * @var null
     */
    protected $adminUser = null;

    /**
     * @var null
     */
    protected $user222 = null;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->client = static::createClient();
        $this->client->enableProfiler();
        $this->clientFrom = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'UserRestFrom',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->clientFrom->followRedirects();
        $this->clientFrom->enableProfiler();
        $this->clientTo = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'UserRestTo',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->clientTo->enableProfiler();
        $this->clientChange = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'changePassword',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->clientChange->enableProfiler();
        $this->clientProfile = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'User111',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->clientProfile->enableProfiler();

        $this->user = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'User',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->user->enableProfiler();

        $this->adminUser = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'adminUser',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->adminUser->enableProfiler();

        $this->user222 = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'Use222r',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->user222->enableProfiler();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        static::$kernel->getContainer()->get('doctrine')->getConnection()->close();
        $this->em->close();
        $this->em = null; // avoid memory leaks
        parent::tearDown();

    }
}