<?php

namespace AppBundle\Controller;

use AppBundle\Annotation\Paid;
use AppBundle\Entity\Event;
use AppBundle\Entity\Interest;
use AppBundle\Form\ContactType;
use AppBundle\Form\SearchType;
use AppBundle\Model\ContactData;
use AppBundle\Model\EmailSettingsData;
use AppBundle\Model\SearchData;
use AppBundle\Traits\CheckAccess;
use LB\PaymentBundle\Entity\Subscriber;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserRelation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class MainController
 * @package AppBundle\Controller
 */
class MainController extends Controller
{
    // check access trait
    use CheckAccess;

    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {
        // get current user
        $currentUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        // check current user
        if($currentUser){
            return $this->redirectToRoute('members');
        }

        $blogs = $em->getRepository("AppBundle:Blog")->findAllBlogs(null, null);
        $slideBlogs = [];
        $i = 0;
        foreach ($blogs as $key =>$value){
            if($key > 0 && $key%5 == 0){
                $i++;
                $slideBlogs[] = [];
            }
            $slideBlogs[$i][] = $value;
        }

        return array('blogs' => $blogs, 'slideBlogs' => $slideBlogs);
    }

    /**
     * @Route("/how-much", name="how-match")
     * @Template()
     * @return array
     */
    public function howMatchAction()
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $publishKey = $this->getParameter('stripe_live') ? $this->getParameter('stripe_publish_key_live') : $this->getParameter('stripe_publish_key_sandbox');

        // get all subscribers
        $subscribers = $em->getRepository('LBPaymentBundle:Subscriber')->findAllSubscribers();

        return array('subscribers' => $subscribers, 'publishKey' => $publishKey);
    }

    /**
     * @param User $user
     * @param $status
     * @Route("/next-user/{id}/{status}" , defaults={"status" = null}, name="next-user")
     * @Security("has_role('ROLE_USER')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function nextUser(Request $request, User $user,  $status = null)
    {
        // get current user
        $currentUser = $this->getUser();

        // get entity manager
        $em = $this->get('doctrine')->getManager();

        // check statuses
        if($status == UserRelation::HIDE || $status == UserRelation::NATIVE){

            // get users relation
            $userRelation = $em->getRepository('LBUserBundle:UserRelation')->findByUsers($currentUser->getId(), $user->getId());

            // check relation
            if (!$userRelation){

                // create user relation if not exist
                $userRelation = new UserRelation();
                $userRelation->setFromUser($currentUser);
                $userRelation->setToUser($user);
            }

            // check user position in relation | fromUser or toUser
            if ($userRelation->getFromUser()->getId() == $currentUser->getId()){

                // check statuses
                $otherStatus = $userRelation->getToStatus();
                $userRelation->setFromStatus($status);
            }
            else {

                // check statuses
                $otherStatus = $userRelation->getFromStatus();
                $userRelation->setToStatus($status);
            }

            // user cannot hide user`s hwo hide it
            if ($status == UserRelation::DENIED && $otherStatus != UserRelation::LIKE){
                throw new HttpException(Response::HTTP_BAD_REQUEST, "You can't denied the user who doesn't like you");
            }
            $em->persist($userRelation);
            $em->flush();
        }


        // get user`s searching params
        $registerData = $currentUser->getSearchingParams();

        // create search data
        $searchData = new SearchData();

        // check register data
        if($registerData) {
            $searchData->interests = $registerData->interests ? $registerData->interests : array();
            $searchData->ageFrom = $registerData->ageFrom;
            $searchData->ageTo = $registerData->ageTo;
            $searchData->distance = $registerData->distance;
            $searchData->lookingFor = $registerData->lookingFor;
            $searchData->zipCode = $registerData->zipCode;

            $city = $registerData->city ? $registerData->city : null;

            // generate city
            if ($city) {
                $coordinates = $city->location;
                $searchData->city = array(
                    'address' => $city->address,
                    'location' => array(
                        "latitude" => $coordinates->latitude,
                        "longitude" => $coordinates->longitude)
                );
            }
        }

        $page = $request->get('page');
        $point = $request->get('point');
        $start = 0;
        if($page && $point){
            $start = (($page - 1) * 18) + $point;
        }

        // get users
        $users = $em->getRepository('LBUserBundle:User')->findUserBySearchData($searchData, $currentUser,
            $start, 1, $user);

        // get result
        $result = $users['query'];

        // check users, if no users redirect to members page
        if(count($result) == 0){
            return $this->redirectToRoute('members');
        }

        // get value from array
        $result = reset($result);
        $point = $point + 1;


        return $this->redirectToRoute('member', array(
            'uid' => $result['user']->getUId(),
            'page' => $page,
            'point' => $point
        ));
    }

    /**
     * @Route("/member/{uid}", defaults={"uid" = -1}, name="member")
     * @Template
     * @Security("has_role('ROLE_USER')")
     * @param $uid
     * @return array
     * @param Request $request
     */
    public function singleMemberAction($uid, Request $request)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'member', $request)){
            return $error;
        }

        // get referer
        $referer = $request->headers->get('referer');

        // if from admin, show disable users
        if(strpos($referer, 'admin') !== false){

            // get filter
            $filters = $em->getFilters();

            // check and disable filter
            $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;
        }

        // empty value for relation
        $userRelationVisitor = null;

        //get current user
        $currentUser = $this->getUser();

        //get currentUserId
        $currentId = $currentUser->getId();

        if ($uid == -1){
            $uid = $currentUser->getUId();
        }

        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);

        // get user by id
        $user = $em->getRepository('LBUserBundle:User')->findUserWithRelationsByUID($uid, $blockUsers);
        if(!$user){
            throw $this->createNotFoundException('User not found');
        }

        $id = $user->getId();

        // if id is current users, dont add visits
        if($id != $currentUser->getId()) {

            //get user relation visitor by id and status
            $userRelationVisitor = $em->getRepository('LBUserBundle:UserRelation')->findByUsers($id, $currentId);

            //check if user visit exist
            if ($userRelationVisitor) {

                if ($userRelationVisitor->getFromUser()->getId() == $currentId) {
                    $userRelationVisitor->setFromVisitorStatus(UserRelation::NEW_VISITOR);
                }
                else {
                    $userRelationVisitor->setToVisitorStatus(UserRelation::NEW_VISITOR);
                }

                $em->persist($userRelationVisitor);
                $em->flush();
            }
            else {
                $userRelationVisitor = $this->get('lb_note')->createVisitor($user);

            }
        }

        // get note sender
        $this->get('app.push.note')->sendVisitNote($currentUser, $user);

        return array(
            'user' => $user,
            'userRelation' => $userRelationVisitor,
        );
    }

    /**
     * Now this action is not using
     * @deprecated
     * @Template
     * @Security("has_role('ROLE_USER')")
     */
    public function friendsAction()
    {
        // get current user
        $currentUser = $this->getUser();

        // get entity manager
        $em = $this->getDoctrine();

        // get friends
        $friendUsers = $em->getRepository('LBUserBundle:User')->getUsersFriend($currentUser->getId());

        return array('friendUsers' => $friendUsers);
    }

    /**
     * @Route("/members/", name="members")
     * @Template
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function membersAction(Request $request)
    {
        // get environment
        $env = $this->get('kernel')->getEnvironment();

        // check env, and disable caching
        if($env != 'test'){

            // disable cache control
            header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
            header('Pragma: no-cache'); // HTTP 1.0.
            header('Expires: 0'); // Proxies.

            // this is for all browsers
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', FALSE);
            header('Pragma: no-cache');

            // This one work in Safari but not Chrome
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: Tue, 15 Nov 2007 12:45:26 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");

        }

        return array();
    }
    /**
     * @Route("/search", name="search")
     * @return array
     * @Template
     * @Security("has_role('ROLE_USER')")
     * @param null $interestId
     */
    public function searchAction($interestId = null)
    {
        // new search data
        $searchData = new SearchData();

        // get current user
        $currentUser = $this->getUser();

        // is page redirected
        if(isset($_SERVER['HTTP_CACHE_CONTROL'])){
            $interestId = null;
        }

        // get users search info`s
        $data = $currentUser->getSearchingParams();

        // check users data, and if null, add users registered data
        if(!$data){
            $data['lookingFor'] = $currentUser->getLookingFor();
            $data['city'] = array();
            $interest = array();
        }
        else{
            $interest = $data->interests ? $data->interests : array();
            $data->lookingFor = is_array($data->lookingFor ) ? User::BISEXUAL : $data->lookingFor ;
        }

        $searchData->interests = $interest;


        // generate form
        $form = $this->createForm(new SearchType($this->container), $searchData);
        return array(
            'form'  => $form->createView(), 'searchData' => $data, 'interestId' => $interestId
        );
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/contact", name="contact")
     * @Template()
     */

    public function contactAction(Request $request)
    {
        //create new contact data
        $contactData = new ContactData();

        //set form
        $form = $this->createForm(new ContactType(), $contactData);

        // check method
        if($request->isMethod("POST")){

            $form->handleRequest($request);

            //check if form data valid
            if ($form->isValid()) {

                //get to email in parameter
                $toEmail = $this->getParameter('to_report_email');

                // create message text
                $messageContent = sprintf("name: %s \r\n username: %s \r\n email: %s \r\n subject: %s \r\n device: %s \r\n  message: %s \r\n",
                    $contactData->getName(), $contactData->getUserName(), $contactData->getEmail(), $contactData->getSubject(), $contactData->getDevice(), $contactData->getMessage());

                $mandrill = $this->container->get("app.mandrill");

                try{
                    // send email via mandrill
                    $mandrill->sendEmail($toEmail, "Mike", 'luvbyrd', $messageContent);

                    $response = new JsonResponse("", Response::HTTP_OK);
                    $responseHeaders = $response->headers;
                    $responseHeaders->set('AMP-Access-Control-Allow-Source-Origin', 'https://www.luvbyrd.com');

                    return $response;
                }
                catch(\Exception $e){

                    $result[] = 'Something Went Wrong. Please Try again Later';
                    return new JsonResponse($result, Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                $errors = $form->getErrors(true);
                $result = array();

                // loop for errors
                foreach($errors as $error){
                    $result[] = $error->getMessage();
                }
                return new JsonResponse($result, Response::HTTP_BAD_REQUEST);
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/remove-image/{filename}/{object}", name="remove_image")
     * @Security("has_role('ROLE_SUPER_ADMIN', 'ROLE_USER')")
     * @param $filename
     * @param $object
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function removeImageAction($filename, $object)
    {
        try{
            // get entity manager
            $em = $this->getDoctrine()->getManager();

            // get object by className
            $object = $em->getRepository($object)->findOneBy(array('fileName' => $filename));

            // get origin file path
            $filePath = $object->getAbsolutePath() . $object->getFileName();

            // get doctrine
            $em = $this->getDoctrine()->getManager();

            // check file and remove
            if (file_exists($filePath) && is_file($filePath)){
                unlink($filePath);
            }

            $object->setFileName(null);
            $object->setFileOriginalName(null);

            $em->persist($object);
            $em->flush();

            $url = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '/';

            return $this->redirect($url);
        }
        catch(\Exception $e){
            throw $e;
        }

    }

    /**
     * @Paid(plan=Subscriber::LIKE)
     * @Route("/like", name="users_like")
     * @Template
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function likeAction(Request $request)
    {
        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'users_like', $request)){
            return $error;
        }

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get currentUSer
        $currentUser = $this->getUser();

        // get ad manager
        $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findLikesUsers($currentUser);

        // update users like
        $em->getRepository("LBUserBundle:UserRelation")->updateLikesUsers($currentUser, null, $userRelations);

        // get page count
        $pageCount = $this->getParameter('page_count');

        //get pagination in container
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $userRelations,
            $this->get('request')->query->get('page', 1),
            $pageCount
        );

        return array('userRelations' => $pagination);
    }

    /**
     * @Paid(plan=Subscriber::LIKE)
     * @Route("/like-by-me", name="like_by_me")
     * @Template
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function likeByMeAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'like_by_me', $request)){
            return $error;
        }

        // get currentUSer
        $currentUser = $this->getUser();

        // get users, like by me
        $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findLikeByMeUsers($currentUser);

        // get page count
        $pageCount = $this->getParameter('page_count');

        //get pagination in container
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $userRelations,
            $this->get('request')->query->get('page', 1),
            $pageCount
        );

        return array('userRelations' => $pagination);
    }

    /**
     * @Route("/connections", name="users_connections")
     * @Template
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function connectionAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'users_connections', $request)){
            return $error;
        }

        // get currentUSer
        $currentUser = $this->getUser();

        // get friends
        $friendUsers = $em->getRepository('LBUserBundle:User')->getUsersFriend($currentUser->getId(), true);

        // update new likes
        $em->getRepository("LBUserBundle:UserRelation")->updateLikesUsers($currentUser, $friendUsers, null,  false);

        // get page count
        $pageCount = $this->getParameter('page_count');

        //get pagination in container
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $friendUsers,
            $this->get('request')->query->get('page', 1),
            $pageCount
        );

        return array('friendUsers' => $pagination);
    }

    /**
     * @Paid(plan=Subscriber::VISITOR)
     * @Route("/visitor", name="visitor")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function visitorAction(Request $request)
    {
        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'visitor', $request)){
            return $error;
        }

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get currentUSerId
        $userId = $this->getUser()->getId();

        // get visitor users by userId
        $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findVisitorUsers($userId);

        //update visitor user status in user relation
        $em->getRepository("LBUserBundle:UserRelation")->updateVisitorUsers($this->getUser(), null, $userRelations);

        // get page count
        $pageCount = $this->getParameter('page_count');

        //get pagination in container
        $paginator  = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
            $userRelations,
            $this->get('request')->query->get('page', 1),
            $pageCount
        );

        return array('pagination' => $pagination);
    }

    /**
     * @Paid(plan=Subscriber::FAVORITE)
     * @Route("/favorite", name="favorite")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function favoriteAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'favorite', $request)){
            return $error;
        }

        // get currentUSerId
        $userId = $this->getUser()->getId();

        // get favorite users by userId
        $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findFavoriteUsers($userId);

        //update favorite user status in user relation
        $em->getRepository("LBUserBundle:UserRelation")->updateFavoriteUsers($this->getUser(), null, $userRelations);

        //get pagination in container
        $paginator  = $this->get('knp_paginator');

        // get page count
        $pageCount = $this->getParameter('page_count');

        $pagination = $paginator->paginate(
            $userRelations,
            $this->get('request')->query->get('page', 1),
            $pageCount
        );

        return array('pagination' => $pagination);
    }


    /**
     * @Paid(plan=Subscriber::FAVORITE)
     * @Route("/favorite-by-me", name="favorite-by-my")
     * @Template()
     * @param Request $request
     * @Security("has_role('ROLE_USER')")
     * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function favoriteByMeAction(Request $request)
    {
        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'favorite-by-my', $request)){
            return $error;
        }

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get currentUSerId
        $userId = $this->getUser()->getId();

        // get favorite users by userId
        $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findFavoriteByMeUsers($userId);

        //get pagination in container
        $paginator  = $this->get('knp_paginator');

        // get page count
        $pageCount = $this->getParameter('page_count');

        $pagination = $paginator->paginate(
            $userRelations,
            $this->get('request')->query->get('page', 1),
            $pageCount
        );

        return array('pagination' => $pagination);
    }

    /**
     * @Route("/events", name="events")
     * @Template
     */
    public function eventsAction()
    {
        // get current user
        $currentUser = $this->getUser();
        
        return array('user' => $currentUser);
    }

    /**
     * @param $event
     * @return array
     * @Route("/event/{id}", name="event")
     * @Template
     */
    public function eventAction(Event $event)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $currentUser = $this->getUser();

        $publishKey = $this->getParameter('stripe_live') ? $this->getParameter('stripe_publish_key_live') : $this->getParameter('stripe_publish_key_sandbox');
        
        $count = $event->getUsersCount();
        $users = $em->getRepository('AppBundle:Event')->getEventUsers($event->getId(), 3, $currentUser?$currentUser->getId():null);

        return array(
            'user' => $currentUser,
            'publishKey' => $publishKey,
            'event' => $event,
            'usersCount' => $count,
            'users' => $users
        );
    }

    /**
     * @param $slug
     * @return array
     * @Route("/{slug}/", name="page")
     * @Template()
     * @throws
     */
    public function pageAction($slug)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get page
        $page = $em->getRepository('AppBundle:Page')->findOneBy(array('slug' => $slug));

        // check page
        if($page){
            return array('page' => $page);
        }

        // check blog
        $blog = $em->getRepository('AppBundle:Blog')->findOneBy(array('slug' => $slug));

        // check page
        if($blog){
            return $this->render("AppBundle:Blog:show.html.twig",  array('blog' => $blog));
        }

        throw $this->createNotFoundException('Page not found');
    }

}
