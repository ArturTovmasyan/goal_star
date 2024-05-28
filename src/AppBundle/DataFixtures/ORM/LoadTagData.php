<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Tag;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTagData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // create tag
        $tag = new Tag();
        $tag->setName('Tag1');
        $tag->setSlug('tag1');
        $manager->persist($tag);

        // create tag
        $tag = new Tag();
        $tag->setName('Tag2');
        $tag->setSlug('tag2');
        $manager->persist($tag);

        // create tag
        $tag = new Tag();
        $tag->setName('Tag3');
        $tag->setSlug('tag3');
        $manager->persist($tag);

        // create tag
        $tag = new Tag();
        $tag->setName('Tag4');
        $tag->setSlug('tag4');
        $manager->persist($tag);

        $manager->flush();

        $this->addReference('tag', $tag);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5; // the order in which fixtures will be loaded
    }
}