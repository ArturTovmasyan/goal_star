<?php

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\LBGroup;
use AppBundle\Entity\LBGroupMembers;
use AppBundle\Entity\LBGroupModerators;
use AppBundle\Entity\Report;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use LB\NotificationBundle\Entity\Notification;
use LB\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @Rest\RouteResource("Group")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class GroupController extends FOSRestController
{
    const CHANGE_DATE = 'changeDate';
    const CHANGE_MONTH = 'changeMonth';
    const CHANGE_YEAR = 'changeYear';

    /**
     * This function get users for select2.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get users for select2",
     *  statusCodes={
     *         202="Returned when created",
     *         404="Return when user not found",
     *     }
     * )
     * @param $date
     * @param $type
     * @return LBGroup
     * @Rest\View(serializerGroups={"lb_group"})
     */
    public function getCalendarAction(Request $request,$date, $type)
    {
        $eventName = $request->get('eventName');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // empty data for cnt
        $cnt = 0;

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get current date
        $date = new \DateTime($date);

        $currentDate = new \DateTime('now');

        $start = $request->get('start') ? (int)$request->get('start') : 0;
        $count = $request->get('count') ? (int)$request->get('count') : 6;

        switch ($eventName)
        {
            case (GroupController::CHANGE_DATE):
                // get requested day group
                $data = $em->getRepository('AppBundle:LBGroup')->findByCalendarDataByDay($date->format('Y-m-d'));
                break;
            default:
                    if ($date->format('m') == $currentDate->format('m') && $date->format('y') == $currentDate->format('y')) {

                        $lastDay = clone $currentDate;
                        $maxDay = $lastDay->modify('+1 month');
                        $maxDay = $maxDay->modify('first day of this month');
                        // get data by parameters
                        $data = $em->getRepository('AppBundle:LBGroup')->findByCalendarData($currentUser, $type, $currentDate, $maxDay, $start, $count);

                } else {
                    //cloned date
                    $lastDay = clone $date;
                    $firstDay = clone $date;

                    // modified date
                    $firstDay = $firstDay->modify('first day of this month');
                    $maxDay = $lastDay->modify('last day of this month');
                    $maxDay->setTime(23, 59, 59);
                    // get group data by month
                    $data = $em->getRepository('AppBundle:LBGroup')->findByCalendarData($currentUser, $type, $firstDay, $maxDay, $start, $count);
                }

                if($type == "group_list"){
                    $cnt = $data['count'];
                    $data = $data['data'];
                }
                break;
        }

        // get full name service
        $fullNameService = $this->get('app.full_name');

        if ($data) {

            foreach ($data as $key => $group) {
                $group->groupCount = $cnt;

                $author = $group->getAuthor();

                // set full name
                $author->fullName = $fullNameService->fullNameFilter($group->getAuthor());


                // get path author photo
                $path = $author->getProfileImagePath();

                $cacheVersion = $author->getProfileImageCacheVersion();

                // check has http in path
                if (strpos($path, 'http') === false) {

                    try {
                        $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'groups_author');
                        $cacheManager = $this->get('liip_imagine.cache.manager');
                        $srcPath = $cacheManager->getBrowserPath($path, 'members');
                        $author->imageCachePath = $srcPath . $cacheVersion;
                    } catch (\Exception $e) {
                        // get author image path
                        $author->imageCachePath = $path . $cacheVersion;
                    }

                } else {
                    // get author image path
                    $author->imageCachePath = $path . $cacheVersion;
                }
                // get group image path
                $path = $group->getDownloadLink();
                // check group image path
                if ($group->getFileName()) {

                    // check has http in path
                    if (strpos($path, 'http') === false) {

                        try {
                            $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'groups');
                            $cacheManager = $this->container->get('liip_imagine.cache.manager');
                            $srcPath = $cacheManager->getBrowserPath($path, 'groups');
                            $group->imageCachePath = $srcPath;

                        } catch (\Exception $e) {
                            // get group image path
                            $group->imageCachePath = $path;
                        }
                    } else {
                        // get group image path
                        $group->imageCachePath = $path;
                    }
                }
            }
        }
        // return group data
        return $data;
    }

    /**
     *
     * This function get users for select2.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get users for select2",
     *  statusCodes={
     *         202="Returned when created",
     *         404="Return when user not found",
     *     }
     * )
     * @Rest\View()
     */
    public function cgetUsersAction($group = null, $type = null, Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $users = null;
        $name = $request->get('q');
        // get projectChartfield by project id
        switch ($type) {
            case 'moderator':
                $users = $em->getRepository('LBUserBundle:User')->findModeratorsForSelect2($group, $name, $currentUser);
                break;
            case 'member':
                $users = $em->getRepository('LBUserBundle:User')->findMembersForSelect2($group, $name, $currentUser);
                break;
            default :
                throw new InvalidArgumentException();
        }
        //check data count
        count($users) > 8 ? $more = true : $more = false;

        return array('items' => $users, 'more' => $more, 'status' => 'OK');
    }

    /**
     * This function is used to join or reject Moderator from User.
     *
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function is used to join or reject Moderator from User.",
     *  statusCodes={
     *         202="Returned when created",
     *         404="Return when user not found", },
     *
     *  parameters={
     *      {"name"="group", "dataType"="integer", "required"=true, "description"="group Id"},
     *      {"name"="status", "dataType"="boolean", "required"=true, "description"="Status boolean"},
     * }
     * )
     *
     * @param Request $request
     * @return LBGroupModerators
     *
     * @Rest\View(serializerGroups={"lb_group"})
     * @throws
     */
    public function postModeratorAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get Entity manager
        $em = $this->getDoctrine()->getManager();
        // get data
        $obj = json_decode($request->getContent());

        // check data
        if ($obj != null) {
            // get group id from content
            $groupId = $obj->group;
            // get group status from content
            $status = $obj->status;
        } elseif ($request->get('group') != null
            && $request->get('status') != null
        ) {
            // get group id from request
            $groupId = $request->get('group');
            // get status from request
            $status = $request->get('status');
        } else {
            // if data not found return exeption
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Request data is missing. Please check data and try again ");
        }

        if ($groupId) {
            // get user id
            $user = $this->getUser();

            if (isset($groupId) && $groupId != null) {
                // create/remove moderator
                $this->createLBGroupModerators($user->getId(), $groupId, $status);

                $em->flush();
            }
            // check status
            if ($status === 1 || $status === true) {
                // get group moderators by user and group id's
                $result = $em->getRepository('AppBundle:LBGroupModerators')->findUniqueData($user->getId(), $groupId);
            } else {
                // get user whose removed from group
                $result = $this->getUser();
            }
            // return moderators if created or user whose removed
            return $result;
        } else {
            // throw exception if group id not found
            throw new NotFoundHttpException("Group id not found in request. Please add group id and try again.");
        }
    }

    /**
     * This function is used to join or reject Moderator from Author.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function is used to create Moderator from Author.",
     *  statusCodes={
     *         202="Returned when created",
     *         404="Return when user not found" },
     * parameters={
     *      {"name"="groupId", "dataType"="integer", "required"=true, "description"="group Id"},
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User`s Id"},
     *      {"name"="status", "dataType"="boolean", "required"=true, "description"="Status boolean"},
     * }
     * )
     *
     * @Rest\View(serializerGroups={"lb_group"})
     */
    public function postAdminModeratorAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // create array from json by request content data
        $obj = json_decode($request->getContent());
        // check object data
        if ($obj != null) {
            $groupId = $obj->groupId;
            $userId = $obj->userId;
            $status = $obj->status;
        } elseif ($request->get('groupId') != null
            && $request->get('userId') != null
            && $request->get('status') != null
        ) {
            $groupId = $request->get('groupId');
            $userId = $request->get('userId');
            $status = $request->get('status');
        } else {
            //throw not found exception if data can'n finde
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Request data is missing. Please check data and try again ");
        }

        if (isset($groupId) && $groupId != null) {

            if (isset($userId) && $userId != null && isset($status)) {
                // get moderator
                $this->createLBGroupModerators($userId, $groupId, $status);
            } else {
                throw new InvalidArgumentException();
            }
        }

        $em->flush();

        if ($status == 1 || $status == true) {

            $result = $em->getRepository('AppBundle:LBGroupModerators')->findUniqueData($userId, $groupId);
        } else {
            $result = $this->getUser();
        }

        return $result;

    }

    /**
     * This function is used to join or reject Member from User.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function is used to join or reject Members from User.",
     *  statusCodes={
     *         202="Returned when created",
     *         404="Return when user not found",
     *     },
     * parameters={
     *      {"name"="group", "dataType"="integer", "required"=true, "description"="group Id"},
     *      {"name"="status", "dataType"="boolean", "required"=true, "description"="Status boolean"},
     * }
     * )
     *
     * @Rest\View(serializerGroups={"lb_group"})
     */
    public function postMemberAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $em = $this->getDoctrine()->getManager();
        $obj = json_decode($request->getContent());

        if ($obj != null) {
            $groupId = $obj->group;
            $status = $obj->status;
        } elseif ($request->get('group') != null
            && $request->get('status') != null
        ) {
            $groupId = $request->get('group');
            $status = $request->get('status');
        } else {
            // if data not found return exeption
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Request data is missing. Please check data and try again ");
        }

        if ($groupId) {
            // get user id
            $user = $this->getUser();

            if (isset($groupId) && $groupId != null && isset($status)) {
                // create/update/remove group members
                $this->createLBGroupMembers($user->getId(), $groupId, $status);

                $em->flush();
            }
            // check data by request status
            if ($status === 1) {
                // if created return members list by group
                $result = $em->getRepository('AppBundle:LBGroupMembers')->findUniqueData($user->getId(), $groupId);
            } else {
                // if removed user from list return removed user
                $result = $this->getUser();
            }
            // return data
            return $result;

        } else {
            // if data not found return exeption
            throw new HttpException(Response::HTTP_BAD_REQUEST, "group id in request data is missing. Please check data and try again. ");
        }
    }

    /**
     * This function is used to join or reject Members from Author.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function is used to create Members from Author.",
     *  statusCodes={
     *         202="Returned when created",
     *         404="Return when user not found" },
     *  parameters={
     *      {"name"="groupId", "dataType"="integer", "required"=true, "description"="group Id"},
     *      {"name"="userId", "dataType"="integer", "required"=true, "description"="User`s Id"},
     *      {"name"="status", "dataType"="boolean", "required"=true, "description"="Status boolean"},
     * }
     * )
     * @Rest\View(serializerGroups={"lb_group"})
     */
    public function postAdminMembersAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // create array by content json data
        $obj = json_decode($request->getContent());

        if ($obj != null) {
            $groupId = $obj->groupId;
            $userId = $obj->userId;
            $status = $obj->status;
        } elseif ($request->get('groupId') != null
            && $request->get('userId') != null
            && $request->get('status') != null
        ) {
            $groupId = $request->get('groupId');
            $userId = $request->get('userId');
            $status = $request->get('status');
        } else {
            // if data not found return exception
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Request data is missing. Please check data and try again ");
        }

        if ($groupId != null) {
            if (isset($userId) && $userId != null) {
                // create/remove member
                $this->createLBGroupMembers($userId, $groupId, $status);
            } else {

                // if data not found return exeption
                throw new HttpException(Response::HTTP_BAD_REQUEST, "user is missing.");
            }

            $em->flush();
        }

        if ($status === 1) {
            // if created return members list by group
            $result = $em->getRepository('AppBundle:LBGroupMembers')->findUniqueData($userId, $groupId);
        } else {
            // if removed user from list return removed user
            $result = $this->getUser();
        }
        // return data
        return $result;

    }

    /**
     * This function use to create Member
     *
     * @param $userId
     * @param $groupId
     * @param $status
     */
    protected function createLBGroupMembers($userId, $groupId, $status)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();
        // get members by user and group id's
        $members = $em->getRepository('AppBundle:LBGroupMembers')->findUniqueData($userId, $groupId);

        // get lb group
        $group = $em->getRepository("AppBundle:LBGroup")->find($groupId);
        //get Moderator
        $user = $em->getRepository('LBUserBundle:User')->find($userId);
        if (!$members) {
            // create new group
            $members = new LBGroupMembers();
            $members->setLbGroup($group);
            $members->setMember($user);
        }

        if ($group->isAuthor($this->getUser()) || $group->getType() == LBGroup::GROUP_PUBLIC) {
            // set member status
            $members->setAuthorStatus($status);
        }

        if ($group->isAuthor($this->getUser()) == false) {
            // set member status
            $members->setMemberStatus($status);
        }

        // set data for notifications
        $data = array('name' => $group->getName(), 'slug' => $group->getSlug(), 'evDate' => $group->getEventDate()->format('M.d.Y'));

        if ($status == 1) {
            //set status for Note
            if ($group->isAuthor($this->getUser()) && $members->getMemberStatus() == 1) {
                // create notification confirm from admin
                $status = Notification::CONFIRM_FOR_ADMIN;
            } elseif ($group->isAuthor($this->getUser()) && $members->getMemberStatus() == 0) {
                // create notification invite
                $status = Notification::INVITE;
            } elseif ($members->getAuthorStatus() == 1) {
                // create notification confirm
                $status = Notification::CONFIRM;
            } else {
                // create notification request to admin
                $status = Notification::REQUEST_TO_ADMIN;
            }

            if ($user->getId() == $this->getUser()->getId()) {
                // get author
                $userId = $group->getAuthor()->getId();
            }
            // get notification service for create notification
            $this->container->get('lb_note')->sendNote($userId, $status, $data);
            $em->persist($members);
        } else {
            // get notification by group
            $url = $this->container->get('router')->generate('group_view', array('slug' => $group->getSlug()), true);

            $url = str_replace('//www.', '//', $url);

            $notifications = $em->getRepository('LBNotificationBundle:Notification')->findByUserIdAndGroup($userId, $url);

            if ($notifications != null) {
                foreach ($notifications as $notification) {
                    $em->remove($notification);
                }
                $em->flush();
            }

            $em->remove($members);
        }
    }

    /**
     * This function use to create Moderators
     *
     * @param $userId
     * @param $groupId
     * @param $status
     *
     */
    protected function createLBGroupModerators($userId, $groupId, $status)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();
        // get moderator
        $moderator = $em->getRepository('AppBundle:LBGroupModerators')->findUniqueData($userId, $groupId);

        // get lb group
        $group = $em->getRepository("AppBundle:LBGroup")->find($groupId);
        //get Moderator
        $user = $em->getRepository('LBUserBundle:User')->find($userId);

        if (!$moderator) {
            // create new moderator
            $moderator = new LBGroupModerators();
            $moderator->setLbGroup($group);
            $moderator->setModerator($user);
        }

        if ($group->isAuthor($this->getUser())) {
            // get author
            $moderator->setAuthorStatus($status);
        } else {
            // get modrator
            $moderator->setModeratorStatus($status);
        }

        // create data for Notifications
        $data = array('name' => $group->getName(), 'slug' => $group->getSlug(), 'evDate' => $group->getEventDate()->format('M.d.Y'));

        if ($status == 1) {
            //set status for Note
            if ($group->isAuthor($this->getUser()) && $moderator->getModeratorStatus() == 1) {
                // create notification confirm from admin
                $status = Notification::CONFIRM_FOR_ADMIN;
            } elseif ($group->isAuthor($this->getUser()) && $moderator->getModeratorStatus() == 0) {
                // create notification invite
                $status = Notification::INVITE;
            } elseif ($moderator->getAuthorStatus() == 1) {
                // create notification confirm
                $status = Notification::CONFIRM;
            } else {
                // create notification request from admin
                $status = Notification::REQUEST_TO_ADMIN;
            }

            // call notifications service
            if ($user->getId() == $this->getUser()->getId()) {
                $userId = $group->getAuthor()->getId();
            }
            // get notification service for create notification
            $this->container->get('lb_note')->sendNote($userId, $status, $data);
            // presist moderator
            $em->persist($moderator);
        } else {
            // get url for notification by group
            $url = $this->container->get('router')->generate('group_view', array('slug' => $group->getSlug()), true);

            $url = str_replace('//www.', '//', $url);

            $notifications = $em->getRepository('LBNotificationBundle:Notification')->findByUserIdAndGroup($userId, $url);

            if ($notifications != null) {
                foreach ($notifications as $notification) {
                    $em->remove($notification);
                }
                $em->flush();
            }

            $em->remove($moderator);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function is used to create Members from Author.",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Return when user not authorized" },
     * parameters={
     *      {"name"="name", "dataType"="string", "required"=true, "description"="group name"},
     *      {"name"="image", "dataType"="file", "required"=true, "description"="image of group"},
     *      {"name"="date", "dataType"="date", "required"=true, "description"="event date"},
     *      {"name"="description", "dataType"="text", "required"=true, "description"="Group description"},
     *      {"name"="type", "dataType"="boolean", "required"=true, "description"="Group type is boolean public(0) or privet(1)"},
     *      {"name"="limit", "dataType"="integer", "required"=true, "description"="Group members limit"},
     *      {"name"="location", "dataType"="array", "required"=true, "description"="Group members limit"},
     * }
     * )
     * @param Request $request
     * @return string
     * @Rest\View(serializerGroups={"lb_group", "lb_group_mobile", "lb_group_calendar", "lb_group_single_mobile"})
     *
     */
    public function postAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get Entity manager
        $em = $this->getDoctrine()->getManager();
        $validator = $this->container->get('validator');

        // get data from request
        $data = $request->request->all();
        // get logger
        $logger = $this->get('monolog.logger.group');
        // add data in log channels
        if (is_array($data)) {
            $logData = json_encode($data);
            $logger->addInfo($logData);
        }

        // get slug
        $slug = array_key_exists('slug', $data) ? $data['slug'] : null;

        if ($slug !== null && strlen($slug) > 2) {
            // get group
            $lbGroup = $em->getRepository("AppBundle:LBGroup")->findOneBySlug($slug);

            // check lb group, and return not found if not found
            if (!$lbGroup) {
                return new JsonResponse("Group by {$slug} is not created", Response::HTTP_NOT_FOUND);
            }
        } else {
            // create new group
            $lbGroup = new LBGroup();
        }

        // check data and set in object
        $lbGroup->setName(array_key_exists('name', $data) ? $data['name'] : null);
        $lbGroup->setEventDate(array_key_exists('date', $data) ? new \DateTime($data['date']) : new \DateTime('now'));
        $lbGroup->setDescription(array_key_exists('description', $data) ? $data['description'] : null);
        $lbGroup->setType(array_key_exists('type', $data) ? $data['type'] : false);
        $lbGroup->setAuthor($this->getUser());

        // check locations
        if (array_key_exists('location', $data)) {
            $location = json_decode($data['location']);
            $location = (array)$location;
            $lbGroup->setAddress($location['address']);
            $lbGroup->setLatitude($location['latitude']);
            $lbGroup->setLongitude($location['longitude']);
        }

        if (array_key_exists('limit', $data) ? $data['limit'] : null) {
            $lbGroup->setJoinLimit($data['limit']);
        }

        // get image from request
        $image = $request->files->get('image');

        // set image
        if (!is_null($image)) {
            $lbGroup->setFile($image);
        }

        // check validation
        $errors = $validator->validate($lbGroup);
        if (count($errors) > 0) {
            $stringErorr = (string)$errors;
            return new JsonResponse("Group cannot created/updated missing data. $stringErorr\n
              Please check data and try again", Response::HTTP_BAD_REQUEST);
        } else {
            // persist object
            $em->persist($lbGroup);
            $em->flush();
        }

        return $lbGroup;
    }

    /**
     * This function get LB Groups mobile.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get LB Groups mobile used date and type of group",
     *  statusCodes={
     *         200="get date",
     *         404="Return when group not found",
     *     }
     * )
     * @param $date
     * @param $type
     * @return LBGroup
     * @Rest\View(serializerGroups={"lb_group_mobile"})
     */
    public function getMobileCalendarAction($date, $type)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get current date
        $date = new \DateTime($date);
        // new dateTime
        $currentDate = new \DateTime('now');

        if (($date->format('m') < $currentDate->format('m')) && ($date->format('Y') <= $currentDate->format('Y'))) {
            $data = array();
        } elseif ($date->format('m') == $currentDate->format('m') && $date->format('y') == $currentDate->format('y')) {
            // clone date
            $lastDay = clone $currentDate;
            $maxDay = $lastDay->modify('+1 month');
            $maxDay = $maxDay->modify('first day of this month');
            // get data for mobile
            $data = $em->getRepository('AppBundle:LBGroup')->findByCalendarData($currentUser, $type, $currentDate, $maxDay);
        } else {
            $lastDay = clone $date;
            $firstDay = clone $date;
            // modify date
            $firstDay = $firstDay->modify('first day of this month');
            $maxDay = $lastDay->modify('last day of this month');
            // get data for mobile
            $data = $em->getRepository('AppBundle:LBGroup')->findByCalendarData($currentUser, $type, $firstDay, $maxDay);
        }

        if (!$data || count($data) < 1) {
            return new JsonResponse("Group by {$date->format('Y-M-d')} and type {$type} is not found", Response::HTTP_NOT_FOUND);
        }

        // get full name service
        $fullNameService = $this->get('app.full_name');

        if ($data) {

            foreach ($data as $key => $group) {
                $path = $group->getDownloadLink();
                $group->getAuthor()->fullName = $fullNameService->fullNameFilter($group->getAuthor());
                if ($group->getFileName()) {

                    // check has http in path
                    if (strpos($path, 'http') === false) {
                        $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'groups');
                        $cacheManager = $this->container->get('liip_imagine.cache.manager');
                        $srcPath = $cacheManager->getBrowserPath($path, 'groups');
                        $group->imageCachePath = $srcPath;

                    } else {
                        $group->imageCachePath = $path;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * This function get LB Groups dates for mobile.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get LB Groups dates for mobile.",
     *  statusCodes={
     *         200="get date",
     *         404="Return when group not found",
     *     }
     * )
     * @param $type
     * @return LBGroup
     * @Rest\View(serializerGroups={"lb_group_calendar"})
     */
    public function getMobileCalendarDatesAction($type)
    {
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $userId = $this->getUser()->getId();
        $dates = $em->getRepository('AppBundle:LBGroup')->findCalendarData($userId, $type);

        return $dates;
    }

    /**
     * This function get LB Groups types.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get LB Groups types.",
     *  statusCodes={
     *         200="get date",
     *         404="Return when group not found",
     *     }
     * )
     * @return array
     * @Rest\View()
     */
    public function getTypesAction()
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $data = array('group_list', 'group_invite_list', 'group_joined_list', 'group_hosting_list');

        return $data;
    }

    /**
     * This function get LB Group by group slug for mobile.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get LB Group by group slug for mobile.",
     *  statusCodes={
     *         200="get date",
     *         404="Return when group not found",
     *     }
     * )
     * @param $slug
     * @return LBGroup
     * @Rest\View(serializerGroups={"lb_group_single_mobile"})
     */
    public function getSingleAction($slug)
    {
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $lbGroup = $em->getRepository('AppBundle:LBGroup')->findOneBySlug($slug);

        // comments
        $comments = null;

        if (!$lbGroup) {
            return new JsonResponse("Group by slug: {$slug} is not found", Response::HTTP_NOT_FOUND);
        }

        $settings = array();
        $currentUser = $this->getUser();

        if ($lbGroup->isAuthor($currentUser)) {
            $settings['state'] = 'author';


            $lbGroup = $em->getRepository('AppBundle:LBGroup')->findOneBySlug($slug);
            $members = $lbGroup->getMembers();
            // create doctrine criteria for member
            $criteriaMembers = Criteria::create()
                ->where(Criteria::expr()->eq("authorStatus", true))
                ->andWhere(Criteria::expr()->eq("memberStatus", true));

            $member = $members->matching($criteriaMembers);

            // create doctrine criteria for moderator
            $criteriaMembersRequestFromAdmin = Criteria::create()
                ->where(Criteria::expr()->eq("authorStatus", true))
                ->andWhere(Criteria::expr()->eq("memberStatus", false));
            $memberRequestFromAdmin = $members->matching($criteriaMembersRequestFromAdmin);

            // create doctrine criteria for member requests
            $criteriaMembersRequestToAdmin = Criteria::create()
                ->where(Criteria::expr()->eq("authorStatus", false))
                ->andWhere(Criteria::expr()->eq("memberStatus", true));

            $memberRequestToAdmin = $members->matching($criteriaMembersRequestToAdmin);

            // modified return dates
            $settings['moderators'] = $lbGroup->getModerators();
            $settings['members'] = $member;
            $settings['members_request_from'] = $memberRequestFromAdmin;
            $settings['members_request_to'] = $memberRequestToAdmin;

        } elseif ($lbGroup->isModerator($currentUser)) {
            $settings['state'] = 'moderator';
        } elseif ($lbGroup->isMember($currentUser)) {
            $settings['state'] = 'member';
        } else {

            $settings['state'] = 'guest';

            // get member status
            $memberStatus = $lbGroup->memberStatuses($currentUser);
            $moderatorStatuses = $lbGroup->moderatorStatuses($currentUser);

            // lb group
            if ($lbGroup->getType() == LBGroup::GROUP_PRIVATE) {
                $lbGroup->joinStatus = $memberStatus['member_status'];
            }

            $lbGroup->memberRequestStatus = $memberStatus['author_status'];
            $lbGroup->moderatorRequestStatus = $moderatorStatuses['author_status'];

        }

        if ($lbGroup->isAuthor($currentUser) === false) {
            $lbGroup = $em->getRepository('AppBundle:LBGroup')->findOneBySlug($slug);
            $moderators = $lbGroup->getModerators();
            $members = $lbGroup->getMembers();
            // create Moderators Criteria
            $criteriaModerators = Criteria::create()
                ->where(Criteria::expr()->eq("authorStatus", true))
                ->andWhere(Criteria::expr()->eq("moderatorStatus", true));
            $moderator = $moderators->matching($criteriaModerators);
            // create Members Criteria
            $criteriaMembers = Criteria::create()
                ->where(Criteria::expr()->eq("authorStatus", true))
                ->andWhere(Criteria::expr()->eq("memberStatus", true));

            $member = $members->matching($criteriaMembers);
            // modified return dates
            $settings['moderators'] = $moderator;
            $settings['members'] = $member;
        }
        // get url by group for find notification
        $url = $this->container->get('router')->generate('group_view', array('slug' => $slug), true);

        $url = str_replace('//www.', '//', $url);

        // get notifications by group and user id's
        $notifications = $em->getRepository('LBNotificationBundle:Notification')->findByUserIdAndGroup($this->getUser()->getId(), $url);

        if ($notifications != null) {
            foreach ($notifications as $notification) {
                $em->remove($notification);
            }
            $em->flush();
        }
        // find comments by group
        if ($lbGroup->getType() == LBGroup::GROUP_PUBLIC) {

            $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($lbGroup->getId());
            if ($thread) {
                $t = $this->container->get('fos_comment.manager.comment')->findCommentTreeByThread($thread);

                $comments = $t;
            }
        }

        // return data for mobile
        return array('settings' => $settings, 'lbGroup' => $lbGroup, 'comments' => $comments);
    }

    /**
     * This function get Notifications by LB Group for mobile.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Group",
     *  description="This function get Notifications by LB Group for mobile.",
     *  statusCodes={
     *         200="get date",
     *         404="Return when group not found",
     *     }
     * )
     * @return Notification
     * @Rest\View()
     */
    public function getNotificationsAction()
    {
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if (!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }
        // get user
        $userId = $this->getUser()->getId();
        // get notifications by user and group id's
        $dates = $em->getRepository('LBNotificationBundle:Notification')->findCountByStatus($userId);

        $data = array('remove' => 0, 'invited' => 0, 'confirm' => 0, 'request_to_admin' => 0, 'confirm_from_admin' => 0);

        // calculate notifications count
        foreach ($dates as $i) {
            if (isset($i['remove']) && $i['remove'] == 1) {
                $data['remove'] += (int)$i['remove'];
            } elseif (isset($i['invited']) && $i['invited'] == 1) {
                $data['invited'] += (int)$i['invited'];
            } elseif (isset($i['confirm']) && $i['confirm'] == 1) {
                $data['confirm'] += (int)$i['confirm'];
            } elseif (isset($i['request_to_admin']) && $i['request_to_admin'] == 1) {
                $data['request_to_admin'] += (int)$i['request_to_admin'];
            } elseif (isset($i['confirm_from_admin']) && $i['confirm_from_admin'] == 1) {
                $data['confirm_from_admin'] += (int)$i['confirm_from_admin'];
            }
        }

        return $data;
    }
}