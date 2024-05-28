<?php
namespace LB\NotificationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LB\NotificationBundle\Entity\Notification;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadNotificationData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // get userFrom and userTo
        $userFrom = $manager->getRepository('LBUserBundle:User')->findOneByEmail('userRestFrom@gmail.com');
        $userTo = $manager->getRepository('LBUserBundle:User')->findOneByEmail('userRestTo@gmail.com');

        // create notification
        $notification = new Notification();
        $notification->setFromUser($userTo);
        $notification->setToUser($userFrom);
        $notification->setStatus(1);
        $notification->setContent('You have notification');
        $manager->persist($notification);

        $manager->flush();

        $this->addReference('notification', $notification);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 8; // the order in which fixtures will be loaded
    }
}