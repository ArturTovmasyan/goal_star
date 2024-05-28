<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseClass extends WebTestCase
{
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_REDIRECT = 302;
    const HTTP_STATUS_CREATED = 201;
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
    protected $container = null;

    /**
     * @var null
     */
    protected $adminUser = null;

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
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = static::$kernel->getContainer();
        $this->client = static::createClient();
        $this->client->enableProfiler();
        $this->clientFrom = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'User',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->clientFrom->enableProfiler();
        $this->clientTo = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'User222',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->clientTo->enableProfiler();
        $this->adminUser = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'adminUser',
            'PHP_AUTH_PW'   => 'superAdmin',
        ));
        $this->adminUser->enableProfiler();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $this->container->get('doctrine')->getConnection()->close();
        $this->em->close();
        $this->em = null; // avoid memory leaks
        parent::tearDown();

    }
}