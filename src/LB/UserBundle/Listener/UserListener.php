<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/16
 * Time: 5:04 PM
 */
namespace LB\UserBundle\Listener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping\PreFlush;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class UserListener
 * @package AppBundle\Listener
 */
class UserListener implements ContainerAwareInterface
{
    /**
     * @var
     */
    public $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    /**
     * @param User $user
     * @param PreFlushEventArgs $event
     * @PreFlush()
     */
    public function preFlush(User $user, PreFlushEventArgs $event)
    {
        //check if addEmail exist
        if (!$user->getUId()) {

            // get service
            $appService = $this->container->get('app.luvbyrd.service');

            // get uid
            $uid = $appService->generateUId();

            // set uid
            $user->setUId($uid);
        }
    }
}
