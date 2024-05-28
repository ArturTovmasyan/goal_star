<?php
namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Blog;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadBlogData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        // get tag1, tag2, tag3 and tag4
        $tag1 = $manager->getRepository('AppBundle:Tag')->findOneByName('Tag1');
        $tag2 = $manager->getRepository('AppBundle:Tag')->findOneByName('Tag2');
        $tag3 = $manager->getRepository('AppBundle:Tag')->findOneByName('Tag3');
        $tag4 = $manager->getRepository('AppBundle:Tag')->findOneByName('Tag4');

        $oldPhotoPath = __DIR__ . '/images/leon.jpg';
        $photoPath = __DIR__ . '/../../../../web/uploads/images/photo.jpg';

        // copy photo path
        copy($oldPhotoPath, $photoPath);


        // create blog
        $blog = new Blog();
        $blog->setTitle('Blog12');
        $blog->setSlug('blog12');
        $blog->setContent('content12');
        $blog->addTag($tag1);
        $blog->addTag($tag2);
        $blog->setFileName('photo.jpg');
        $blog->setFileOriginalName('photo.jpg');
        $manager->persist($blog);

        // create blog
        $blog = new Blog();
        $blog->setTitle('Blog13');
        $blog->setSlug('blog13');
        $blog->setContent('content13');
        $blog->addTag($tag1);
        $blog->addTag($tag3);
        $manager->persist($blog);

        // create blog
        $blog = new Blog();
        $blog->setTitle('Blog4');
        $blog->setSlug('blog4');
        $blog->setContent('content43');
        $blog->addTag($tag4);
        $manager->persist($blog);

        $manager->flush();

        $this->addReference('blog', $blog);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}