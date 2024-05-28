<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/30/15
 * Time: 6:24 PM
 */

namespace LB\UserBundle\EventListener;

use AppBundle\Entity\Blog;
use AppBundle\Entity\Interest;
use AppBundle\Entity\LBGroup;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PostLoad;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;

class FileListener
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
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        // get container
        $container = $this->container;
        $cacheVersion = null;

        // set default filter name
        $filterName = 'mobile_list';

        // try to get request
        try{
            $request = $container->get('request');
            $basePath = $request->getSchemeAndHttpHost();

            if($request->getPathInfo() == '/api/v1.0/file/all/file'){
                $filterName = 'mobile';
            }

        }
        // request is inactive(// line is called from command)
        catch(\Exception $e){
            $basePath = User::BASE_PATH;
        }

        $entity = $args->getEntity();
        if($entity instanceof File || $entity instanceof Blog || $entity instanceof LBGroup){

            $cacheManager = $container->get('liip_imagine.cache.manager');

            if($entity instanceof File){
                // get path
                $path = $entity->getUploadDir() . '/' . $entity->getPath();
                $cacheVersion = $entity->getCacheVersion() ?  '?v=' . $entity->getCacheVersion() : null;
            }
            else{
                // get path
                $path = $entity->getDownloadLink();
            }

            try{
                $container->get('liip_imagine.controller')->filterAction($container->get('request'), $path, $filterName);
                $srcPath = $cacheManager->getBrowserPath($path, $filterName);
                $entity->imageFromCache = $srcPath . $cacheVersion;
            }
            catch(\Exception $e){
                $entity->imageFromCache = $basePath . '/' .  $path . $cacheVersion;
            }
        }
    }
}