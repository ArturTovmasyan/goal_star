<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\LBGroup;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadLBGroupData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        // get user
        $userFrom = $manager->getRepository('LBUserBundle:User')->findOneByEmail('user@gmail.com');

        // create LBGroup
        $lbGroup = new LBGroup();
        $lbGroup->setName('LBGroup1');
        $lbGroup->setSlug('lbgroup1');
        $lbGroup->setEventDate(new \DateTime());
        $lbGroup->setDescription('LBGroup1Description');
        $lbGroup->setAddress('address');
        $lbGroup->setLatitude(0);
        $lbGroup->setLongitude(0);
        $lbGroup->setType(0);
        $lbGroup->setAuthor($userFrom);
        $manager->persist($lbGroup);

        // get user
        $userFrom1 = $manager->getRepository('LBUserBundle:User')->findOneByEmail('userRestFrom@gmail.com');

        // create LBGroup
        $lbGroup = new LBGroup();
        $lbGroup->setName('LBGroup2');
        $lbGroup->setSlug('lbgroup2');
        $lbGroup->setEventDate(new \DateTime());
        $lbGroup->setDescription('LBGroup2Description');
        $lbGroup->setAddress('address2');
        $lbGroup->setLatitude(0);
        $lbGroup->setLongitude(0);
        $lbGroup->setType(0);
        $lbGroup->setAuthor($userFrom1);
        $manager->persist($lbGroup);

        $manager->flush();

        $this->addReference('lbGroup', $lbGroup);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 7; // the order in which fixtures will be loaded
    }
}