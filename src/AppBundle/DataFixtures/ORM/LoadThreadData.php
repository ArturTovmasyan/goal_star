<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Thread;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Blog;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadThreadData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        //get group
        $group = $this->getReference('lbGroup');

        $thread = new Thread();
        $thread->setId($group->getId());
        $thread->setPermalink("http://luvbyrd.loc/group/view/tag1");
        $thread->setCommentable(true);
        $thread->setNumComments(3);
        $thread->setLastCommentAt(new \DateTime('now'));
        $manager->persist($thread);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // the order in which fixtures will be loaded
    }
}