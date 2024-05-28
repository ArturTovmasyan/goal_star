<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 6/9/16
 * Time: 1:18 PM
 */

namespace AppBundle\Annotation;

use AppBundle\Controller\MainController;
use LB\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AnnotationDriver
 * @package AppBundle\Annotations
 */
class AnnotationDriver
{

    /**
     * @var
     */
    private $reader;

    /**
     * @param $reader
     */
    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $object = new \ReflectionObject($controller[0]); // get controller as object
        $method = $object->getMethod($controller[1]); // getting controller methods (actions)

        // loop for annotations
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {

            // check is paid annotation
            if($configuration instanceof Paid && $configuration->plan){

                // get controller object
                $controllerObject = $controller[0];

                // check has controller method 'get user', is user
                if(method_exists($controllerObject, 'getUser') && $controllerObject->getUser() instanceof User){

                    // get plan
                    $plan = $configuration->plan;

                    //  get user
                    $user = $controllerObject->getUser();

                    // check has access
                    if(!$user->hasAccessToPlan($plan)){

                        // set controller action to how much
                        $controller[1] = 'howMatchAction';

                        // set controller
                        $event->setController($controller);
                    }

                }
            }
        }
    }
}
