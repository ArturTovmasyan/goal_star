<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/5/16
 * Time: 4:20 PM
 */
namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Event;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Rest\Prefix("/api/v1.0")
 */
class MainController extends FOSRestController
{
    const IOS_REQUEST_PARAM     = 'ios';
    const ANDROID_REQUEST_PARAM = 'android';
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Main",
     *  description="This function is used to get mobile last versions",
     *  statusCodes={
     *         200="Returned when goals was returned",
     *  },
     *  parameters={
     *      {"name"="mobileAppPlatform", "dataType"="string", "required"=true, "description"="mobile app platform"}
     *  }
     * )
     *
     * @param $mobileAppPlatform
     * @return array
     * @Rest\View
     */
    public function getAppVersionAction($mobileAppPlatform)
    {
        switch($mobileAppPlatform){
            case MainController::IOS_REQUEST_PARAM:
                return [
                    'mandatory' => $this->container->getParameter('ios_mandatory_version'),
                    'optional'  => $this->container->getParameter('ios_optional_version')
                ];
            case MainController::ANDROID_REQUEST_PARAM:
                return [
                    'mandatory' => $this->container->getParameter('android_mandatory_version'),
                    'optional'  => $this->container->getParameter('android_optional_version')
                ];
        }
        return [];
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Event",
     *  description="This function is used to get Events",
     *  statusCodes={
     *         200="Returned when events was returned",
     *         400="Bad request",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"events_for_mobile"})
     *
     * @param $start
     * @param $limit
     * @param $request
     * @return mixed
     */
    public function getEventsFreshAction(Request $request,$start, $limit)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $liipManager = $this->get('liip_imagine.cache.manager');

        // get date
        $date = new \DateTime('now');
        
        $id = $request->get('id');
        // get near by goals
        $events = $em->getRepository('AppBundle:Event')->getFreshEvents($date, $start, $limit, $id);

        foreach($events as $key => $event) {
            if ($event->getImagePath()) {
                try {
                    $event->setCachedImage($liipManager->getBrowserPath($event->getImagePath(), (!$id && $key == 1 && count($events) >= 4)?'blog_list_vertical':'blog_list_small'));
                } catch (\Exception $e) {
                    $event->setCachedImage("");
                }
            }
        }

        return $events;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Event",
     *  description="This function is used to get Event by id",
     *  statusCodes={
     *         200="Returned when events was returned",
     *         400="Bad request",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"event_for_mobile"})
     *
     * @param $id
     * @return mixed
     */
    public function getEventAction($id)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get near by goals
        $event = $em->getRepository('AppBundle:Event')->find($id);

        return $event;
    }

    /**
     * @Rest\Get("/api/v1.0/events/{id}/users/{start}/{limit}", name="get_event_users", options={"method_prefix"=false}, defaults={"_format"="json"})
     * @ApiDoc(
     *  resource=true,
     *  section="Event",
     *  description="This function is used to get Event users",
     *  statusCodes={
     *         200="Returned when events was returned",
     *         400="Bad request",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"event_for_mobile"})
     *
     * @param $id
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getEventUsersAction($id, $start, $limit)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get near by goals
        $users = $em->getRepository('AppBundle:Event')->getEventUsersByLimit($id, $start, $limit);

        return $users;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Event",
     *  description="This function is used to connect to Event",
     *  statusCodes={
     *         200="Returned when events was returned",
     *         400="Bad request",
     *  },
     * )
     *
     * @Rest\View(serializerGroups={"event_for_mobile"})
     *
     * @param $id
     * @return Response
     */
    public function putConnectEventAction($id)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get near by goals
        $event = $em->getRepository('AppBundle:Event')->find($id);

        //check if not logged in user
        if(!is_object($event)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "There is not any Event with this id");
        }

        // check have user this ticket
        if($event->getType() == Event::BUY_TYPE){
            return new JsonResponse('This Event is not free', Response::HTTP_BAD_REQUEST);
        }
        
        // check have user this ticket
        if($event->getUsers()->contains($currentUser)){
            return new JsonResponse('You already connect in this event', Response::HTTP_BAD_REQUEST);
        }

        $event->addUser($currentUser);
        $em->flush();

        return new JsonResponse('ok', Response::HTTP_OK);
    }
}