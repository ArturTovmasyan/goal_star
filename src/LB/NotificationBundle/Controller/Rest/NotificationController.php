<?php

namespace LB\NotificationBundle\Controller\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\SecurityExtraBundle\Annotation\Secure;
use LB\NotificationBundle\Entity\Notification;
use LB\UserBundle\Entity\UserRelation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Rest\RouteResource("Notification")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class NotificationController extends FOSRestController
{
    /**
     * This function is used to get notification by user id and status
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to get notification by user id and status",
     *  statusCodes={
     *         400="Bad Request",
     *         404="Return when user not found",
     *         401="Not login user"
     *     }
     * )
     * @param $status
     * @return Response
     * @Rest\View(serializerGroups={"note"})
     */
    public function getNoteByUserIdAndStatusAction($status)
    {
        //check if status not exist
        if ($status != Notification::INVITE   &&
            $status != Notification::CONFIRM  &&
            $status != Notification::CONFIRM_FOR_ADMIN  &&
            $status != Notification::REQUEST_TO_ADMIN &&
            $status != Notification::REMOVE) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Status is not exist");
        }

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check currentUser
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get notification by user id and action status
        $notification = $em->getRepository('LBNotificationBundle:Notification')->findNoteByIdAndStatus($currentUser->getId(), $status);

        return $notification;
    }

    /**
     * This function is used to get notification by user id
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to get notification by user id",
     *  statusCodes={
     *         400="Bad Request",
     *         404="Return when user not found",
     *         401="Not login user"
     *     }
     * )
     * @return Response
     * @Rest\View(serializerGroups={"note"})
     */
    public function getAllNoteByUserAction()
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check currentUser
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get notification by user id
        $notification = $em->getRepository('LBNotificationBundle:Notification')->findAllNoteByUser($currentUser->getId());

        return $notification;
    }

    /**
     * This function is used to get count of notifications;
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to get count of notifications",
     *  statusCodes={
     *         200="Returned when successful",
     *         400="Bad Request",
     *         401="Not login user"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"note"})
     * @return mixed
     * @throws
     */
    public function getCountAction()
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if user exist
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }
        // get notification count by userId
        $count = $em->getRepository('LBNotificationBundle:Notification')->findUnReadNoteCount($currentUser->getId());

        return array($count);
    }

    /**
     * This function is used to remove all notifications by given user id ;
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Notification",
     *  description="This function is used to remove all notifications by given user id",
     *  statusCodes={
     *         204="No Content",
     *         400="Bad Request",
     *         401="Not login user"
     *     }
     * )
     *
     * @param $noteId
     * @return mixed
     * @throws
     */
    public function deleteAction($noteId)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if user exist
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        try {

            // remove all notification by userId
            $em->getRepository('LBNotificationBundle:Notification')->removeNote($currentUser->getId(), $noteId);
        }
        catch (\Exception $e){
            return new Response('Can not delete note', Response::HTTP_BAD_REQUEST);
        }
        return new Response(Response::HTTP_NO_CONTENT);
    }

}