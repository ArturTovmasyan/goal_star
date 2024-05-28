<?php

namespace LB\UserBundle\Controller\Rest;


use AppBundle\Model\EmailSettingsData;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserRelation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Rest\RouteResource("UserRelation")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class UserRelationController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserRelation",
     *  description="This function is used to get user relations count",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Return when user not authorized",
     *     }
     * )
     * @return Response
     * @Rest\View()
     * @throws
     */
    public function getCountAction($status)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        if(is_string($status)) {
            switch ($status) {
                case "like":
                    $status = UserRelation::LIKE;
                    break;
                case "connections":
                    $status = UserRelation::FRIEND;
                    break;
                case "favorite":
                    $status = UserRelation::FAVORITE;
                    break;
                case "visitor":
                    $status = UserRelation::VISITOR;
                    break;
                case "like_by_me":
                    $status = UserRelation::LIKED_BY_ME;
                    break;
                case "favorite-by-my":
                    $status = UserRelation::FAVORITE_BY_ME;
                    break;
                default:
                    $status = null;
                    break;
            }
        }

        //check userId
        if(!is_numeric($status)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid status parameter");
        }

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get count
        $count = $em->getRepository("LBUserBundle:UserRelation")->findUsersCountByAction($currentUser, $status);


        return new JsonResponse($count, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserRelation",
     *  description="This function is used to update visitor users new status",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Return when user not authorized",
     *     }
     * )
     * @return Response
     * @Rest\View()
     * @throws
     */
    public function postUpdateNewVisitorAction()
    {

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get current userId
        $userId = $currentUser->getId();

        //check userId
        if(!(int)$userId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid user id parameter");
        }

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get toUser
        $toUser = $em->getRepository('LBUserBundle:User')->find($userId);

        //check if to user not found
        if(!$toUser) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "User by id $userId not found");
        }

        //update visitor users newVisitor status
        $em->getRepository('LBUserBundle:UserRelation')->updateVisitorUsers($userId);

        return new Response("", Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="UserRelation",
     *  description="This function is used to create new favorite users",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Return when user not authorized",
     *
     *     }
     * )
     * @param $id
     * @param $status
     * @return Response
     * @Rest\View()
     * @throws
     */
    public function putFavoriteAction($id, $status)
    {
        if(is_string($status)) {
            switch ($status) {
                case "favorite":
                    $status = UserRelation::NEW_FAVORITE;
                    break;
                case "unfavorite":
                    $status = UserRelation::NATIVE;
                    break;
                default:
            }
        }
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get current user id
        $currentId = $currentUser->getId();

        //check status
        if(!(int)$status) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid status parameter");
        }

        //check to user id
        if(!(int)$id) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid user id parameter");
        }

        //get toUser
        $toUser = $em->getRepository('LBUserBundle:User')->find($id);

        //check if to user not found
        if(!$toUser) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "User by id $id not found");
        }

        if($id != $currentId) {

            //get user relation visitor by id and status
            $userRelationFavorite = $em->getRepository('LBUserBundle:UserRelation')->findByUsers($id, $currentId);

            //check if user favorite exist
            if ($userRelationFavorite) {

                if ($userRelationFavorite->getFromUser()->getId() == $currentId) {
                    $userRelationFavorite->setFromFavoriteStatus($status);
                }
                else {
                    $userRelationFavorite->setToFavoriteStatus($status);
                }

                $em->persist($userRelationFavorite);
                $em->flush();
            }
            else {
                $this->get('lb_note')->createFavorite($id);

            }

            //check if status New Favorite
            if($status == UserRelation::NEW_FAVORITE)
            {
                //send email for user about favorite
                $this->get('app.email')->sendEmail($id, EmailSettingsData::ACCEPT_FRIEND_REQUEST);
            }
        }


        // check status, and send push note
        if($status == UserRelation::NEW_FAVORITE){
            $this->get('app.push.note')->sendFavoriteNote($currentUser, $toUser);
        }

        return new Response("", Response::HTTP_NO_CONTENT);
    }

    /**
     * This function is used to change User`s conversation status
     *
     * @ApiDoc(
     *  resource=true,
     *  section="UserRelation",
     *  description="This function is used to change User status",
     *  statusCodes={
     *         200="Returned when status changed",
     *         400="Returned when no such status code, or duplicated entity, or user is current user",
     *         404="Return when user not found with such id",
     *         401="Return when user not authorized"
     *     }
     * )
     *
     * @param User $user
     * @param $status
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Rest\View()
     * @throws
     */
    public function getConversationAction(User $user, $status)
    {
        // check status
        if(!is_numeric($status)) {

            switch ($status) {
                case "favorite":
                    $status = UserRelation::FAVORITE;
                    break;
                case "spam":
                    $status = UserRelation::SPAM;
                    break;
                case "native":
                    $status = UserRelation::NATIVE;
                    break;
                default:
                    return new JsonResponse('status must be 10|1|7|favorite|spam|native', Response::HTTP_BAD_REQUEST);
                    break;
            }
        }
        elseif($status != UserRelation::FAVORITE &&  $status != UserRelation::SPAM && $status != UserRelation::NATIVE ){
            return new JsonResponse('status must be 10|1|7|favorite|spam|native',Response::HTTP_BAD_REQUEST);
        }

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // check user with current user
        if ($currentUser->getId() == $user->getId()){

            return new JsonResponse('You can not conversate himself',Response::HTTP_BAD_REQUEST);
        }


        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get users relation
        $userRelation = $em->getRepository('LBUserBundle:UserRelation')->findByUsers($currentUser->getId(), $user->getId());

        // if no relations
        if (!$userRelation){

            // create new user
            $userRelation = new UserRelation();
            $userRelation->setFromUser($currentUser);
            $userRelation->setToUser($user);
        }

        // check users
        if ($userRelation->getFromUser()->getId() == $currentUser->getId()){
            $userRelation->setFromConversation($status);
        }
        else {
            $userRelation->setToConversation($status);

        }

        $em->persist($userRelation);
        $em->flush();

        return new Response('', Response::HTTP_OK);
    }
}