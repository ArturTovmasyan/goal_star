<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 11/3/15
 * Time: 6:51 PM
 */
namespace LB\MessageBundle\Controller\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use LB\UserBundle\Controller\Rest\UserController;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserRelation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Model\EmailSettingsData;

/**
 * @Rest\RouteResource("Message")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class MessageController extends FOSRestController
{
    /**
     * This function is used to get current user id
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to get current user id",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Not authorized user"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"message"})
     */
    public function cgetAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        // get id
        $id = $request->get('id', null);

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get result
        $results = $this->getMessageUser($currentUser, $request, false, $id);

        return $results;

//        //get current user
//        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
//
//        //check if not logged in user
//        if(!is_object($currentUser)) {
//            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
//        }
//
//        $start = $request->get('start') ? (int)$request->get('start') : 0;
//        $count = $request->get('count') ? (int)$request->get('count') : 100;
//        $id = $request->get('id', null);
//
//        $em = $this->getDoctrine()->getManager();
//
//        $messageUsers = array();
//
//        // check is admin
//        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? true: false;
//
//        // get favorites users and ids
//        $favoritesMessages = $em->getRepository("LBUserBundle:User")->findFavoriteConversations($currentUser, $isAdmin);
//        $favoriteIds = array_keys($favoritesMessages);
//
//        // get new messages and ids
//        $newMessageUsers = $em->getRepository('LBMessageBundle:Message')
//            ->findNewMessageUsers($currentUser->getId(),$this->getParameter('lb_message_user'), $isAdmin);
//        $newMessagesIds = array_keys($newMessageUsers);
//
//        // get ids
//        $ids = $favoriteIds + $newMessagesIds;
//
//        // if is first page, add favorites and new messages
//        if($start == 0){
//
//            $messageUsers = $messageUsers + $favoritesMessages;
//            $messageUsers = $messageUsers + $newMessageUsers;
//            $count = 100 - count($ids);
//        }
//        $allMessageUsers = $em->getRepository('LBMessageBundle:Message')
//            ->findMessageUsers($currentUser->getId(),$this->getParameter('lb_message_user'), $ids, $isAdmin, $start, $count);
//
//        $messageUsers = $messageUsers + $allMessageUsers;
//
//        $unreadMessagesCountsByUsers = $em->getRepository('LBMessageBundle:Message')
//            ->findUnreadCountByUsers($currentUser->getId(), $start, $count);
//
//        if($id){
//            $messages = $em->getRepository('LBMessageBundle:Message')->findUsersMessages($currentUser->getId(), $id, 0, 1);
//            if(!$messages){
//                $newChatUser = $em->getRepository('LBUserBundle:User')->findUserForNewChat($id, $isAdmin);
//                if(is_array($newChatUser)){
//                    $newChatUser = reset($newChatUser);
//                    array_unshift($messageUsers, $newChatUser);
//                }
//            }
//        }


//        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
//
//        $imagePath = $currentUser->getProfileImagePath();
//        $imagePath = strpos($imagePath, 'http') === false ? $request->getSchemeAndHttpHost() . $imagePath : $imagePath;
//
//        $user = array('id' => $currentUser->getId(),
//            'first_name' => $currentUser->getFirstName(),
//            'last_name' => $currentUser->getLastName(),
//            'profile_image_path' => $imagePath,
//            'message_image' => $imagePath
//        );

//        return array_values($messageUsers);
    }

    /**
     * This function is used to get current user id
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to get current user id",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Not authorized user"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function getUserIdAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $imagePath = $currentUser->getProfileImagePath();

        $cacheVersion = $currentUser->getProfileImageCacheVersion();

        // get cache manager
        $cacheManager = $this->get('liip_imagine.cache.manager');

        // check profile image
        if($imagePath){

            // check has http in path
            if(strpos($imagePath, 'http') === false){

                try{
                    $this->get('liip_imagine.controller')->filterAction($this->get('request'), $imagePath, 'friends');
                    $srcPath = $cacheManager->getBrowserPath($imagePath, 'friends');
                }
                catch(\Exception $e){
                    $srcPath = $imagePath . $cacheVersion;
                }
            }
            else{
                $srcPath = $imagePath . $cacheVersion;
            }
        }


//        $imagePath = strpos($imagePath, 'http') === false ? $request->getSchemeAndHttpHost() . $imagePath : $imagePath;

        // check is admin
        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? true: false;

        $user = array(
            'id' => $currentUser->getId(),
            'uid' => $currentUser->getUId(),
            'first_name' => $currentUser->getFirstName(),
            'last_name' => $isAdmin ? $currentUser->getLastName() : '',
            'profile_image_path' => $srcPath,
            'message_image' => $srcPath
        );

        return $user;
    }

    /**
     * This function is used to get messages
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to get messages",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Not authorized user"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"message", "message_fromUser", "message_toUser", "user"})
     */
    public function getLimitedMessagesAction($toUserId, $start = 0, $count = 20)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('LBMessageBundle:Message')
            ->findUsersMessages($toUserId, $currentUser->getId(), $start, $count);

        return $messages;
    }


    /**
     * This function is used to get messages
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to get messages",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Not authorized user"
     *     },
     *  parameters={
     *      {"name"="start", "dataType"="integer", "required"=false, "description"="start |by default 0"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="start |by default 20"}
     * }
     * )
     *
     * @Rest\View(serializerGroups={"message", "message_fromUser", "message_toUser", "for_mobile"})
     */
    public function getLimitedMobileAction(Request $request, $toUserId)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $start = $request->get('start') ? (int)$request->get('start') : 0;
        $count = $request->get('count') ? (int)$request->get('count') : 20;

        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('LBMessageBundle:Message')
            ->findUsersMessages($toUserId, $currentUser->getId(), $start, $count);

        $userRelation = $em->getRepository("LBUserBundle:UserRelation")->findByUsers($currentUser->getId(), $toUserId);
        $isFavorite = false;

        // if is relation
        if($userRelation){
            $favoriteStatus = $userRelation->getFromUser()->getId() == $currentUser->getId() ? $userRelation->getFromConversation() : $userRelation->getToConversation();

            if($favoriteStatus == UserRelation::FAVORITE){
                $isFavorite = true;
            }
        }

        return array('isFavorite' => $isFavorite, "messages" => $messages);
    }

    /**
     * This function is used to send email
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to send email",
     *  statusCodes={
     *         200="Ok",
     *         401="Not authorized user"
     *     }
     * )
     * @param $userId
     * @return Response
     */
    public function getEmailAction($userId)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //check to user id
        if(!(int)$userId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid user id parameter");
        }

        //send email for new messages
        $this->get('app.email')->sendEmail($userId, EmailSettingsData::NEW_MESSAGE);

        return new Response(Response::HTTP_OK);
    }

    /**
     * This function is used to send email
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to send email",
     *  statusCodes={
     *         200="Ok",
     *         401="Not authorized user"
     *     }
     * )
     * @param $userId
     * @return Response
     */
    public function getPushAction($userId)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //check to user id
        if(!(int)$userId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid user id parameter");
        }

        // get entity repository
        $em = $this->getDoctrine()->getManager();

        // get user
        $user = $em->getRepository("LBUserBundle:User")->find($userId);

        //check to user id
        if(!$user) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid user id parameter");
        }

        // get send push note
        $this->get('app.push.note')->sendEmailNote($currentUser, $user);

        return new Response(Response::HTTP_OK);
    }

    /**
     * This function is used to get user list with last message
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to get user list with last message",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Not authorized user",
     *     },
     *  parameters={
     *      {"name"="start", "dataType"="integer", "required"=false, "description"="start |by default 0"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="start |by default 20"}
     * }
     * )
     *
     * @Rest\View(serializerGroups={"for_mobile"})
     */
    public function getMessageUsersAction(Request $request)
    {

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();



        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get result
        $results = $this->getMessageUser($currentUser, $request);

        return $results;
    }

    /**
     * @param User $currentUser
     * @param Request $request
     * @param bool|true $mobile
     * @param null $id
     * @return array
     */
    private function getMessageUser(User $currentUser, Request $request, $mobile = true, $id = null)
    {
        // empty value for result
        $results = array();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get unread messages count users
        $counts = $em->getRepository('LBMessageBundle:Message')
            ->findUnreadCountByUsers($currentUser->getId());

        $start = $request->get('start') ? (int)$request->get('start') : UserController::START;
        $count = $request->get('count') ? (int)$request->get('count') : UserController::COUNT;

        // user blocks
        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);

        //get message users
        $messageUsers = $em->getRepository('LBMessageBundle:Message')
            ->findUserMessagesForMobile($currentUser->getId(), $start, $count , $blockUsers);

        // check id and get user
        if($id && !$mobile){

            // check is user exist in array
            $userInMessageId = array_search($id,array_map(function($item){return $item['id'];},$messageUsers));

            // check id
            if($userInMessageId === false && $start == 0){

                // generate data for new user
                $firstData = array(
                    'id' => $id,
                    'favorite' => 8,
                    'content' => null,
                    'created' => null
                );

                // add to new user
                array_unshift($messageUsers, $firstData);
            }

            // check is find in array
            if($userInMessageId !== false){

                // get data
                $firstData = $messageUsers[$userInMessageId];

                // unset from array
                unset($messageUsers[$userInMessageId]);

                // if is first request, add to first
                if($start == 0){
                    array_unshift($messageUsers, $firstData);
                }
            }
        }

        $ids = array_map(function($item){return $item['id']; }, $messageUsers);


        $users = $em->getRepository("LBUserBundle:User")->findById($ids);

        // value for selected user
        $selectedUser = null;


        if($messageUsers) {
            foreach ($messageUsers as $key => $messageUser) {
                $id = $messageUser['id'];
                $user = array_key_exists($id, $users) ? $users[$id] : null;

                if(!$user){
                    continue;
                }

                $content = $messageUser['content'];
                $created  = $messageUser['created'];
                $favorite = $messageUser['favorite'];
                $newMsgCount = null;

                if(array_key_exists($id, $counts)) {
                    $newMsgCount = $counts[$id];
                }

                    $data['user'] = $user;
                    $data['favorite'] = $favorite;
                    $data['msgCount'] = $newMsgCount;

                if($mobile){
                    $data['content'] = $content;
                    $data['created'] = $created;
                }

                $results[] = $data;
            }
        }

        return $results;
    }


    /**
     * This function is used to delete message
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Message",
     *  description="This function is used to delete message",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Not authorized user",
     *     }
     * )
     *
     * @param $id
     * @return JsonResponse
     * @Rest\View()
     */
    public function deleteAction($id)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get message
        $message = $em->getRepository('LBMessageBundle:Message')
            ->findUserMessage($currentUser->getId(), $id);

        // check message
        if(!$message){
            return new JsonResponse('Message not found', Response::HTTP_NOT_FOUND);
        }

        // delete message
        $message->setIsDeleted(true);

        $em->persist($message);
        $em->flush();

       return new JsonResponse('', Response::HTTP_OK);
    }

}