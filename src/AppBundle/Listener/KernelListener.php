<?php

/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 3:16 PM
 */

namespace AppBundle\Listener;

use AppBundle\Controller\Rest\MainController;
use LB\UserBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class KernelListener
 * @package AppBundle\Listener
 */
class KernelListener
{
    /**
     * @var
     */
    private $container;

    /**
     * @var
     */
    private $mandatoryVersions;

    /**
     * @param $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mandatoryVersions = array(
            MainController::IOS_REQUEST_PARAM     => $this->container->getParameter('ios_mandatory_version'),
            MainController::ANDROID_REQUEST_PARAM => $this->container->getParameter('android_mandatory_version')
        );
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest($event)
    {
        // get user
        $token = $this->container->get('security.token_storage')->getToken();

        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // get url name
        $routeName = $this->container->get('request')->get('_route');

        // get request
        $request = $event->getRequest();

        $mobileAppVersion  = $request->query->get('mobileAppVersion'); // get mobile app version
        $mobileAppPlatform = $request->query->get('mobileAppPlatform'); // get mobile app platform

        // check data
        if ($mobileAppVersion && $mobileAppPlatform){
            if(isset($this->mandatoryVersions[$mobileAppPlatform]) &&
                version_compare($mobileAppVersion, $this->mandatoryVersions[$mobileAppPlatform]) == -1) {

                $event->setResponse(new Response('You need to update your app', Response::HTTP_UPGRADE_REQUIRED));
            }
        }

        // check token
        if(method_exists($token, 'getUser') && $routeName){

            // get user
            $user = $token->getUser();

            // if is user
            if($user instanceof User){

                // get request
                $request = $this->container->get('request');

                // get session
                $session = $request->getSession();

                // get date
                $date = new \DateTime('now');

                // set to session
                $session->set($user->getId(), $date);

                // get session activity
                $sessionActivity = $session->get($user->getId());

                // get user last activity
                $dbActivity = $user->getLastActivity();

                // check if different is more than 5 minutes
                if(is_null($dbActivity) || date_diff($sessionActivity, $dbActivity)->i >= 5){

                    // update last activity
                    $em->getRepository("LBUserBundle:User")->updateActivity($date, $user->getId());
                }

                // if is rest, don`t continue
                if (strpos($request->getRequestUri(), '/api/') !== false) {
                    return;
                }

                // allow user only one page
                if($user->getSearchVisibility() && $routeName!= 'disable_account' && $routeName != 'email_settings'){

                    // get router
                    $router = $this->container->get('router');

                    // generate url
                    $url = $router->generate('email_settings');

                    //set flash messages for open login by js
                    $this->container->get('session')->getFlashBag()->add('forbiddenError', 'forbidden_Error');

                    // set response
                    $event->setResponse(new RedirectResponse($url));
                }
            }
        }
    }
}