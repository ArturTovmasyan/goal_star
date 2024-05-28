<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LBGroup;
use AppBundle\Entity\LBGroupMembers;
use AppBundle\Entity\LBGroupModerators;
use AppBundle\Form\LBGroupType;
use FOS\UserBundle\Entity\Group;
use JMS\Serializer\Annotation\SerializedName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class GroupController
 * @package AppBundle\Controller
 * @Route("/group")
 * @Security("has_role('ROLE_USER')")
 */
class GroupController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="group_list")
     * @Template()
     */
    public function indexAction()
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current date
        $date = new \DateTime('now');

        // get all groups
        $groups = $em->getRepository('AppBundle:LBGroup')->findAllForList($date);

        return array("groups" => $groups);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/create/{slug}", defaults={"slug" = null}, name="group_create")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @param null $slug
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request, $slug = null)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        // check id
        if($slug){

            // get lb group
            $lbGroup = $em->getRepository("AppBundle:LBGroup")->findOneBySlug($slug);

            $this->denyAccessUnlessGranted('edit', $lbGroup, 'Unauthorized access!');

            // check lb group, and return not found if not found
            if(!$lbGroup){
                throw $this->createNotFoundException("group not found");
            }

            // for init
            $loc = array('address' => $lbGroup->getAddress(), 'location' => array('latitude' => $lbGroup->getLatitude(), 'longitude' => $lbGroup->getLongitude()));
            $loc = json_encode($loc);
        }
        else{
            // new lb group
            $lbGroup = new LBGroup();
            $loc = null;
        }

        // get location name
        $locationName = $lbGroup->getAddressName() ? $lbGroup->getAddressName() : $lbGroup->getAddress();

        // create form
        $form = $this->createForm(new LBGroupType(), $lbGroup);

        // check method
        if($request->isMethod("POST")){

            $loc = $request->request->get('location');
            $locationName = $request->get('location-name');

            // get data from request
            $form->handleRequest($request);

            //check valid of form
            if($form->isValid()){

                // get location
                $loc = json_decode($loc);
                // set current user
                $lbGroup->setAuthor($currentUser);

                if($loc) {
                    $lbGroup->setAddress(isset($loc->address) ? $loc->address : null);
                    $lbGroup->setLatitude(isset($loc->location->latitude) ? $loc->location->latitude: null);
                    $lbGroup->setLongitude(isset($loc->location->longitude) ? $loc->location->longitude : null );
                    $lbGroup->setAddressName($locationName);
                }


                $em->persist($lbGroup);
                $em->flush();
                // redirect to view
                return $this->redirect($this->generateUrl('group_view', array('slug'=>$lbGroup->getSlug())));
            }
        }

        return array('form' => $form->createView(), 'loc' => $loc, 'locationName' => $locationName);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/view/{slug}", name="group_view")
     * @ParamConverter(name="group", class="AppBundle:LBGroup", options={"repository_method" = "getOneById"})
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param LBGroup $group
     */
    public function showAction(LBGroup $group)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();
        $isComment = $this->isGranted('view', $group);

        $url = $this->container->get('router')->generate('group_view', array('slug' => $group->getSlug()), true);
        $url = str_replace('//www.', '//', $url);

        $notifications = $em->getRepository('LBNotificationBundle:Notification')->findByUserIdAndGroup($this->getUser()->getId(), $url);

        if($notifications != null) {

            foreach($notifications as $notification) {

                $em->remove($notification);
            }
            $em->flush();
        }

        // get full name service
        $fullNameService = $this->get('app.full_name');

        // check group
        if($group){

            $path = $group->getDownloadLink();
            $group->getAuthor()->fullName = $fullNameService->fullNameFilter($group->getAuthor());
            if($group->getFileName()){

                try{
                    // check has http in path
                    if(strpos($path, 'http') === false){
                        $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'group_single');
                        $cacheManager = $this->container->get('liip_imagine.cache.manager');
                        $srcPath = $cacheManager->getBrowserPath($path, 'group_single');
                        $group->imageCachePath = $srcPath;
                    }
                    else{
                        $group->imageCachePath = $path;
                    }
                }
                catch(\Exception $e){
                    $group->imageCachePath = $path;
                }
            }
        }

        return array('group' => $group,  'is_comment'=>$isComment);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/invite/list", name="group_invite_list")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function invitedAction()
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get user
        $user = $this->getUser();
        // get invited groups data
        $date = new \DateTime('now');
        $groups = $em->getRepository('AppBundle:LBGroup')->findInvitedByUser($user->getId(), $date);

        return array('groups'=>$groups);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/join/list", name="group_joined_list")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function joinedAction()
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get user
        $user = $this->getUser();
        // get invited groups data
        $date = new \DateTime('now');
        $groups = $em->getRepository('AppBundle:LBGroup')->findJoinedByUser($user->getId(), $date);

        return array('groups'=>$groups);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/hosting/list", name="group_hosting_list")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function hostingAction()
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get user
        $user = $this->getUser();
        // get invited groups data
        $date = new \DateTime('now');
        $groups = $em->getRepository('AppBundle:LBGroup')->findHostingByUser($user->getId(), $date);

        return array('groups'=>$groups);
    }

    /**
     *
     * This function get group events date and group name
     *
     * @param $type
     * @return JsonResponse
     * @Route("/calendar", name="group_calendar")
     * @Security("has_role('ROLE_USER')")
     */
    public function calendarAction($type)
    {
        $em = $this->getDoctrine()->getManager();

        $userId = $this->getUser()->getId();
        $dates = $em->getRepository('AppBundle:LBGroup')->findCalendarData($userId, $type);

        return new JsonResponse($dates);
    }

    /**
     * This function get group events date and group name
     *
     * @Route("/notification", name="group_notification")
     * @Security("has_role('ROLE_USER')")
     * @param null $routeName
     * @return Response
     */
    public function notificationAction($routeName = null)
    {
        $em = $this->getDoctrine()->getManager();

        $groups = $em->getRepository('AppBundle:LBGroup')->findOld();

        $urls = array();
        foreach($groups as $group) {

           if($group['slug']) {

               $url = $this->container->get('router')->generate('group_view', array('slug' => $group['slug']), true);;
               $url = str_replace('//www.', '//', $url);

                $urls[] = $url;
            }
        }

        $em->getRepository('LBNotificationBundle:Notification')->removeByUrl($urls);

        $userId = $this->getUser()->getId();
        $dates = $em->getRepository('LBNotificationBundle:Notification')->findCountByStatus($userId);

        $data = array('remove'=> 0, 'invited'=>0, 'confirm'=>0, 'request_to_admin'=>0, 'confirm_from_admin'=>0);

        $groupNotes = array();

        foreach($dates as $i) {

            $link = $i['link']; // get note url
            $explodeLink = explode('/', $link); // explode by /
            $link = is_array($explodeLink) ? end($explodeLink) : $link; // get last item in array, or all url

            if(isset($i['remove']) && $i['remove'] == 1) {
                $data['remove'] += (int)$i['remove'];
            }
            elseif(isset($i['invited']) && $i['invited'] == 1) {
                $data['invited'] += (int)$i['invited'];

                // check is invited page
                if($routeName == "group_invite_list"){

                    //  if slug is exist, add 1
                    if(array_key_exists($link, $groupNotes)){
                        $groupNotes[$link]++;
                    }else{ // if slug is not exist
                        $groupNotes[$link] = 1;
                    }
                }

            }
            elseif(isset($i['confirm']) && $i['confirm'] == 1) {
                $data['confirm'] += (int)$i['confirm'];

                // check is invited page
                if($routeName == "group_hosting_list"){

                    //  if slug is exist, add 1
                    if(array_key_exists($link, $groupNotes)){
                        $groupNotes[$link]++;
                    }else{ // if slug is not exist
                        $groupNotes[$link] = 1;
                    }
                }
            }
            elseif(isset($i['request_to_admin']) && $i['request_to_admin'] == 1) {
                $data['request_to_admin'] += (int)$i['request_to_admin'];

                // check is hosting page
                if($routeName == "group_hosting_list"){

                    //  if slug is exist, add 1
                    if(array_key_exists($link, $groupNotes)){
                        $groupNotes[$link]++;
                    }else{ // if slug is not exist
                        $groupNotes[$link] = 1;
                    }
                }
            }
            elseif(isset($i['confirm_from_admin']) && $i['confirm_from_admin'] == 1) {
                $data['confirm_from_admin'] += (int)$i['confirm_from_admin'];

                // check is joined page
                if($routeName == "group_joined_list"){

                    //  if slug is exist, add 1
                    if(array_key_exists($link, $groupNotes)){
                        $groupNotes[$link]++;
                    }else{ // if slug is not exist
                        $groupNotes[$link] = 1;
                    }
                }
            }
        }

        $this->get('twig')->addGlobal('groupNotes', $groupNotes);

        return $this->render('@App/Group/group_menue.html.twig', array('data'=>$data, 'routeName' => $routeName));
    }

    /**
     * @Route("/delete/{id}", name="group_delete")
     * @Security("has_role('ROLE_USER')")
     * @param LBGroup $group
     * @ParamConverter(name="id", class="AppBundle:LBGroup")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(LBGroup $group)
    {
        // get current user
        $currentUser = $this->getUser();

        // get author
        $author = $group->getAuthor();

        // check author with id
        if($author->getId() == $currentUser->getId()){

            // get entity manager
            $em = $this->getDoctrine()->getManager();

            $em->remove($group);
            $em->flush();

            return $this->redirectToRoute('group_list');
        }

        throw $this->createNotFoundException("You can not delete this goal");
    }
}
