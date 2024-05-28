<?php
namespace LB\MessageBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LB\MessageBundle\Entity\Message;
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
        $userMassage2 = $manager->getRepository('LBUserBundle:User')->findOneByEmail('userMassage2@gmail.com');
        $userMassage3 = $manager->getRepository('LBUserBundle:User')->findOneByEmail('userMassage3@gmail.com');

        // create message
        $message = new Message();
        $message->setFromUser($userFrom);
        $message->setToUser($userTo);
        $message->setSubject('message');
        $message->setContent('message Content');
        $message->setCreated(new \DateTime('2016-01-15 11:06:15'));
        $manager->persist($message);

        // create message
        $message = new Message();
        $message->setFromUser($userTo);
        $message->setToUser($userFrom);
        $message->setSubject('message1');
        $message->setContent('message1');
        $message->setCreated(new \DateTime('2016-01-15 12:06:15'));
        $manager->persist($message);

        // create message
        $message = new Message();
        $message->setFromUser($userMassage3);
        $message->setToUser($userFrom);
        $message->setSubject('message3');
        $message->setContent('message3');
        $message->setCreated(new \DateTime('2016-01-14 11:06:15'));
        $manager->persist($message);

        // create message
        $message = new Message();
        $message->setFromUser($userMassage2);
        $message->setToUser($userFrom);
        $message->setSubject('message2');
        $message->setContent('message2');
        $message->setCreated(new \DateTime('2016-01-13 11:07:35'));
        $manager->persist($message);

        $manager->flush();

        $this->addReference('message', $message);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 9; // the order in which fixtures will be loaded
    }
}