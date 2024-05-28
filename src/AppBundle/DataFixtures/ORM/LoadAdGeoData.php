<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\AdGeo;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAdGeogData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $adGeo = new AdGeo();
        $adGeo->setDescription('<p>this is just for testing</p>');
        $adGeo->setRadius(1);
        $adGeo->setName('Test GEO Ad');

        $manager->persist($adGeo);

        $adGeo = new AdGeo();
        $adGeo->setDescription('<p>hi I want to invite you&nbsp;</p>');
        $adGeo->setRadius(10);
        $adGeo->setName('Just for testing');

        $manager->persist($adGeo);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 12; // the order in which fixtures will be loaded
    }
}