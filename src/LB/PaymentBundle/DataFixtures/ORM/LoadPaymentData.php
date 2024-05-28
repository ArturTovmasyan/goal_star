<?php
namespace LB\PaymenBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LB\NotificationBundle\Entity\Notification;
use LB\PaymentBundle\Entity\Subscriber;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPaymentData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // create subscriber
        $subscriber = new Subscriber();
        $subscriber->setDescription('Good price');
        $subscriber->setStripeId(11);
        $subscriber->setStripePlan(array(
            'name' => 'Unlimited package',
            'amount' => 100,
            'currency' => 'USD',
            'interval' => 1,
            'intervalCount' => 1
        ));

        $manager->persist($subscriber);

        // create subscriber
        $subscriber = new Subscriber();
        $subscriber->setDescription('Test price');
        $subscriber->setStripeId(1);
        $subscriber->setStripePlan(array(
            'name' => 'Good package',
            'amount' => 50,
            'currency' => 'USD',
            'interval' => 1,
            'intervalCount' => 1
        ));

        $manager->persist($subscriber);

        // create subscriber
        $subscriber = new Subscriber();
        $subscriber->setDescription('Bad price');
        $subscriber->setStripeId(5);
        $subscriber->setStripePlan(array(
            'name' => 'Good package',
            'amount' => 80,
            'currency' => 'USD',
            'interval' => 1,
            'intervalCount' => 1
        ));

        $manager->persist($subscriber);

        $manager->flush();

    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 11; // the order in which fixtures will be loaded
    }
}