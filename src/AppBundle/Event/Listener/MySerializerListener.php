<?php

namespace AppBundle\Event\Listener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use LB\PaymentBundle\Entity\Subscriber;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;


/**
 * Class MySerializerListener
 * @package AppBundle\Event\Listener
 */
class MySerializerListener implements EventSubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            array('event' => 'serializer.post_serialize', 'class' => 'LB\UserBundle\Entity\User', 'method' => 'onPostSerialize'),
            array('event' => 'serializer.post_serialize', 'class' => 'LB\PaymentBundle\Entity\Subscriber', 'method' => 'onPostSerialize'),
        );
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        // get container
        $container = $this->container;

        // get token
        $token = $container->get('security.token_storage')->getToken();

        // get object
        $object = $event->getObject();

        // get group
        $group = $event->getContext()->attributes->get('groups');

        $groupArray = $group->isDefined() ? $group->get('value') : array();

        // check user
        if($object instanceof User){

            // get lastName
            $lastName = $object->getLastName();
            $path = $object->getProfileImagePath();

            $cacheVersion = $object->getProfileImageCacheVersion();

            // check token and user
            if(is_object($token) && is_object($token->getUser())){

                // get admin
                $isAdmin = $container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');

                // check is current user or is admin
//                if($currentUser->getId() != $object->getId()  && $isAdmin === false){
                if($isAdmin === false && !in_array('show_last_name', $groupArray)){
                    $lastName = '';
                }
            }

            $event->getVisitor()->addData('last_name', $lastName);

            try{

                if(in_array('message', $groupArray)){

                    $container->get('liip_imagine.controller')->filterAction($container->get('request'), $path, 'friends');
                    $cacheManager = $container->get('liip_imagine.cache.manager');
                    $srcPath = $cacheManager->getBrowserPath($path, 'friends');
                    $srcPath = $srcPath . $cacheVersion;
                    $event->getVisitor()->addData('message_image', $srcPath);
                }
            }
            catch(\Exception $e){
                $srcPath = $path . $cacheVersion;
                $event->getVisitor()->addData('message_image', $srcPath);
            }
        }

        // check user
        if($object instanceof Subscriber){
            $planInfo = $object->getPlanInfo();
//            if(!is_array($planInfo))
            {
                $planInfo['amount'] = $planInfo['amount']/100;
                $planInfo['currency'] = '$';
                $planInfo['name'] = ucwords($planInfo['name']);
            }

            $event->getVisitor()->addData('plan_info', $planInfo);
        }
    }
}