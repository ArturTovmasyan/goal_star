<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Interest;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadInterestData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $interestGroup = $manager->getRepository('AppBundle:InterestGroup')->findOneByName('interestGroup1');

        // create interest
        $interest = new Interest();
        $interest->setName('Interest1');
        $interest->setGroup($interestGroup);
        $interest->setPosition(1);
        $manager->persist($interest);

        // create interest
        $interest = new Interest();
        $interest->setName('Interest2');
        $interest->setGroup($interestGroup);
        $interest->setPosition(2);
        $manager->persist($interest);

        // create interest
        $interest = new Interest();
        $interest->setName('Interest3');
        $interest->setGroup($interestGroup);
        $interest->setPosition(3);
        $manager->persist($interest);

        $manager->flush();

        $this->addReference('interest', $interest);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}