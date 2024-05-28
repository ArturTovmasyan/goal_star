<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/16
 * Time: 5:16 PM
 */
namespace AppBundle\Listener;
use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Class MyEntityListenerResolver
 * @package AppBundle\Listener
 */
class MyEntityListenerResolver extends DefaultEntityListenerResolver
{
    /**
     * @var Container
     */
    public $container;
    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($className)
    {
        // get object
        $object = parent::resolve($className);

        // check object, and set container
        if($object instanceof ContainerAwareInterface){
            $object->setContainer($this->container);
        }

        return $object;
    }
}

