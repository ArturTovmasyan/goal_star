<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 11/2/15
 * Time: 5:28 PM
 */
namespace LB\UserBundle\Controller\Rest;


use AppBundle\Model\EmailSettingsData;
use AppBundle\Model\SearchData;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use LB\MessageBundle\Entity\Message;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserAdLocation;
use LB\UserBundle\Entity\UserPush;
use LB\UserBundle\Entity\UserRelation;
use RMS\PushNotificationsBundle\Message\AndroidMessage;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @Rest\RouteResource("User")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class UserController extends FOSRestController
{
    const START = 0;
    const COUNT = 20;

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function postStep2Action(Request $request )
    {

        return array("results" => []);
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function postStep3Action(Request $request )
    {

        return array("results" => []);
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"slider"})
     */
    public function getSwiperAction(Request $request )
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get all users form slider
        $allUsers = $em->getRepository('LBUserBundle:User')->findAllWitJoin($currentUser);

        // check all users
        if($allUsers){

            // get cache manager
            $cacheManager = $this->get('liip_imagine.cache.manager');

            // loop for user
            foreach($allUsers as $user){

                // get path
                $path = $user->getProfileImagePath();
                $cacheVersion = $user->getProfileImageCacheVersion();

                // check profile image
                if($path){

                    // check has http in path
                    if(strpos($path, 'http') === false){

                        try{
                            $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'members');
                            $srcPath = $cacheManager->getBrowserPath($path, 'members');
                            $user->imageCachePath = $srcPath . $cacheVersion;
                        }
                        catch(\Exception $e){
                            $user->imageCachePath = $path . $cacheVersion;
                        }
                    }
                    else{
                        $user->imageFromCache = $path . $cacheVersion;
                    }
                }
            }
        }

        return $allUsers;

    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     * @deprecated
     * @Rest\View()
     */
    public function getSearchAction(Request $request )
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get search data
        $search = $request->get('q');

        // get subscribers
        $users = $em->getRepository('LBUserBundle:User')->findAllForSelect2($search);

        return array("results" => $users);
    }

    /**
     * This function is used to get current user info
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to get one user by id",
     *  statusCodes={
     *         200="Returned when status changed",
     *         404="Return when user not found with such id",
     *         401="Access allowed only for registered users"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"user_for_mobile"})
     */
    public function getInfoAction()
    {

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }
        return $currentUser;
    }

    /**
     * his function is used to change User status
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to change User status",
     *  statusCodes={
     *         200="Returned when status changed",
     *         400="Returned when no such status code, or duplicated entity, or user is current user",
     *         404="Return when user not found with such id",
     *         401="Access allowed only for registered users"
     *     }
     * )
     *
     * @param User $user
     * @param $status
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Rest\View()
     * @throws
     */
    public function getStatusAction($user, $status)
    {
        if(is_string($status)) {
            switch ($status) {
                case "block":
                    $status = UserRelation::BLOCK;
                    break;
                case "unblock":
                    $status = UserRelation::NATIVE;
                    break;
                case "like":
                    $status = UserRelation::LIKE;
                    break;
                case "unlike":
                    $status = UserRelation::NATIVE;
                    break;
                case "hide":
                    $status = UserRelation::HIDE;
                    break;
            }
        }
        $em = $this->getDoctrine()->getManager();
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);
        $user = $em->getRepository('LBUserBundle:User')->findUserWithRelations($user, $blockUsers);

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "User not found");
        }

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        if ($status != UserRelation::BLOCK  &&
            $status != UserRelation::LIKE   &&
            $status != UserRelation::DENIED &&
            $status != UserRelation::NATIVE &&
            $status != UserRelation::HIDE ){
            throw new HttpException(Response::HTTP_BAD_REQUEST, "No such status code");
        }

        if ($currentUser->getId() == $user->getId()){
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Give status to current user");
        }


        $userRelation = $em->getRepository('LBUserBundle:UserRelation')->findByUsers($currentUser->getId(), $user->getId());

        if (!$userRelation){
            $userRelation = new UserRelation();
            $userRelation->setFromUser($currentUser);
            $userRelation->setToUser($user);
        }

        if ($userRelation->getFromUser()->getId() == $currentUser->getId()){
            $otherStatus = $userRelation->getToStatus();
            $userRelation->setFromStatus($status);

            if($status == UserRelation::LIKE){
                $userRelation->setIsLikeReadFrom(false);

                if( $userRelation->getToStatus() == UserRelation::LIKE){
                    $userRelation->setIsLikeReadTo(false);
                }
            }

        }
        else {
            $otherStatus = $userRelation->getFromStatus();
            $userRelation->setToStatus($status);

            if($status == UserRelation::LIKE){
                $userRelation->setIsLikeReadTo(false);

                if($userRelation->getFromStatus() == UserRelation::LIKE){
                    $userRelation->setIsLikeReadFrom(false);
                }

            }
        }

        if ($status == UserRelation::DENIED && $otherStatus != UserRelation::LIKE){
            throw new HttpException(Response::HTTP_BAD_REQUEST, "You can't denied the user who doesn't like you");
        }

        $em->persist($userRelation);
        $em->flush();

        //send email after like or double like
        if($status == UserRelation::LIKE) {

            // // get note sender/ send like note
            $this->get('app.push.note')->sendLikeNote($currentUser, $user);

            if($userRelation->getFromStatus() == UserRelation::LIKE || $userRelation->getFromStatus() == UserRelation::LIKE) {

                $this->get('app.email')->sendEmail($user->getId(), EmailSettingsData::SEND_FRIEND_REQUEST);
            }

            if($userRelation->getFromStatus() == UserRelation::LIKE && $userRelation->getToStatus() == UserRelation::LIKE){

                // get note sender/ send connection note
                $this->get('app.push.note')->sendConnectionNote($currentUser, $user);
                $this->get('app.push.note')->sendConnectionNote($user, $currentUser);
            }
        }

        return new Response('', Response::HTTP_OK);
    }

    /**
     * This function is used to get users
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to get all user",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     },
     *  parameters={
     *      {"name"="start", "dataType"="integer", "required"=false, "description"="start |by default 0"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="start |by default 20"}
     *  }
     * )
     * @Rest\View(serializerGroups={"for_mobile"})
     */
    public function cgetAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        $start = $request->get('start') ? (int)$request->get('start') : self::START;
        $count = $request->get('count') ? (int)$request->get('count') : self::COUNT;

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get users
        $users = $em->getRepository('LBUserBundle:User')->findAllWitJoin($currentUser, $start , $count);

        return $users;
    }

    /**
     * This function is used to get users for slider
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to get all user for slider",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     }
     * )
     * @Rest\View(serializerGroups={"for_mobile"})
     * @param $start
     * @param $count
     * @return mixed
     */
    public function getSliderAction($start, $count)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);

        // get users
        $users = $em->getRepository('LBUserBundle:User')->findAllWitCountForSlider($currentUser, $start, $count, $blockUsers);

        return $users;
    }

    /**
     * This function is used to get user by action status
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to to get one user by id",
     *  statusCodes={
     *         200="Returned when status changed",
     *         404="Return when user not found with such id",
     *         401="Access allowed only for registered users"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"user_for_mobile"})
     */
    public function getAction($id)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get current user id
        $currentUserId = $currentUser->getId();

        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);

        // get user by id
        $user = $em->getRepository('LBUserBundle:User')->findUserWithRelations($id, $blockUsers);

        // check user
        if(!$user){

            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }

        //set default status
        $block = false;
        $like = false;
        $favorite = false;


        // check is current user
        if($currentUserId != $user->getId()){

            //get user relation
            $userRelation = $em->getRepository('LBUserBundle:UserRelation')->findByUsers($currentUserId, $id);

            //check if user relation exist
            if($userRelation) {

                //set like or block status
                $status = $currentUserId == $userRelation->getFromUser()->getId() ? $userRelation->getFromStatus(): $userRelation->getToStatus();

                //set favorite status
                $favoriteStatus = $currentUserId == $userRelation->getFromUser()->getId() ? $userRelation->getFromFavoriteStatus(): $userRelation->getToFavoriteStatus();

                if($status == UserRelation::BLOCK) {
                    $block = true;
                }
                elseif($status == UserRelation::LIKE ) {
                    $like = true;
                }

                if($favoriteStatus == UserRelation::FAVORITE || $favoriteStatus == UserRelation::NEW_FAVORITE) {
                    $favorite = true;
                }

                if ($userRelation->getFromUser()->getId() == $currentUserId) {
                    $userRelation->setFromVisitorStatus(UserRelation::NEW_VISITOR);
                }
                else {
                    $userRelation->setToVisitorStatus(UserRelation::NEW_VISITOR);
                }

                $em->persist($userRelation);
                $em->flush();

            }
            else{
                $this->get('lb_note')->createVisitor($user);
            }

            // send push note
            $this->get('app.push.note')->sendVisitNote($currentUser, $user);
        }

        // get path
        $path = $user->getProfileImagePath();
        $cacheVersion = $user->getProfileImageCacheVersion();
        $cacheManager = $this->get('liip_imagine.cache.manager');

        // check profile image
        if($path){

            // check has http in path
            if(strpos($path, 'http') === false){

                try{
                    $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'mobile');
                    $srcPathForMobile = $cacheManager->getBrowserPath($path, 'mobile');
                    $user->imageFromCache = $srcPathForMobile . $cacheVersion;
                }
                catch(\Exception $e){
                    $user->imageFromCache = $path . $cacheVersion;
                }
            }
            else{
                $user->imageFromCache = $path . $cacheVersion;
            }
        }

        // get files
        $files = $user->getFiles();

        $basePath = $this->container->get('request')->getSchemeAndHttpHost();

        if($files){
            // loop for files
            foreach($files as $file){

                // get path
                $path = $file->getUploadDir() . '/' . $file->getPath();
                $cacheVersion = $file->getCacheVersion() ? '?v=' . $file->getCacheVersion() : null;

                // check has http in path
                if(strpos($path, 'http') === false){

                    try{
                        $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'mobile');
                        $srcPathForMobile = $cacheManager->getBrowserPath($path, 'mobile');
                        $file->imageFromCache = $srcPathForMobile . $cacheVersion;
                    }
                    catch(\Exception $e){
                        $file->imageFromCache = $basePath . '/' .  $path . $cacheVersion;
                    }
                }
                else{
                    $file->imageFromCache = $basePath . '/' .  $path . $cacheVersion;
                }
            }
        }

        //create status array
        $statuses = array('block' =>$block, 'like' =>$like, 'favorite' => $favorite);

        return array('user' => $user, 'statuses' => $statuses);
    }


    /**
     * This function is used to get user by search data
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get user by search data",
     *  statusCodes={
     *         200="Returned when status changed",
     *         404="Return when user not found with such id",
     *         401="Access allowed only for registered users"
     *     },
     * parameters={
     *      {"name"="age", "dataType"="array", "required"=false, "description"="User`s ages from"},
     *      {"name"="lookingFor", "dataType"="integer", "required"=false, "description"="User`s looking for"},
     *      {"name"="interests", "dataType"="array", "required"=false, "description"="User`s interests"},
     *      {"name"="city", "dataType"="string", "required"=false, "description"="User`s city"},
     *      {"name"="distance", "dataType"="string", "required"=false, "description"="User`s distance"},
     *      {"name"="zipCode", "dataType"="string", "required"=false, "description"="User`s zip code"},
     *      {"name"="start", "dataType"="integer", "required"=false, "description"="start |by default 0"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="start |by default 20"}
     *}
     * )
     *
     * @Rest\View(serializerGroups={"search"})
     */
    public function postSearchAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get all data
        $data = $request->request->all();

        // new search data
        $searchData = new SearchData();

        $interestId = array_key_exists('interestId', $data) ? $data['interestId'] : null;

        if($interestId){

            // check interest
            $searchData->interests = array($interestId);
        }
        else{

            // get age from angular
            $age = array_key_exists('age', $data) ? $data['age'] : array();
            $age = is_array($age) ? $age : json_decode($age);

            if(is_array($age) && count($age) > 0){
                // set users data, if exist in request
                $searchData->ageFrom = min($age);
                $searchData->ageTo =  max($age);
            }

            // check is request from mobile
            if(!$age){
                $searchData->ageFrom = array_key_exists('ageFrom', $data) ? $data['ageFrom'] : null;
                $searchData->ageTo = array_key_exists('ageTo', $data) ? $data['ageTo'] : null;
            }

            // get city
            $city = array_key_exists('city', $data) ? $data['city'] : null;

            // for mobile
            if(!is_array($city) && $city){
                $city = json_decode($city, true);
                $city = (array)$city;
            }

            $searchData->city = $city ? $city : null;

            // get ski and ride
            $skiAndRiding = array_key_exists('skiAndRide', $data) ? $data['skiAndRide'] : null;

            // for mobile
            if(!is_array($skiAndRiding) && $skiAndRiding){
                $skiAndRiding = json_decode($city, true);
            }

            $searchData->skiAndRide = $skiAndRiding;

            if(array_key_exists('zipCode', $data)){
                $searchData->zipCode = $data['zipCode'];
            }

            if(array_key_exists('distance', $data)){
                $searchData->distance = $data['distance'];
            }
            elseif(array_key_exists('radius', $data)){
                $searchData->distance = $data['radius'];
            }

            // check looking for
            $lookingFor = null;
            if(array_key_exists('lookingFor', $data)){
                $lookingFor = $data['lookingFor'];
            }
            elseif(array_key_exists('gender', $data)){
                $lookingFor = (int)$data['gender'];
            }

            // if is from mobile
            if(!is_numeric($lookingFor) && !is_array($lookingFor) && $lookingFor){
                $lookingFor = json_decode($lookingFor);
            }

            // check if zip code exist, and distance is not null
            if($searchData->zipCode && $searchData->distance){

                $lvService = $this->get('app.luvbyrd.service');
                $zipObject = $lvService->getZipObjByZipCode($searchData->zipCode);
                $searchData->zipCrd['latitude'] = $zipObject->getLat();
                $searchData->zipCrd['longitude'] = $zipObject->getLng();
            }

            // check looking for
            if(is_array($lookingFor)){

                foreach($lookingFor as $key => $value){

                    // for mobile
                    if(is_numeric($key)){
                        switch($value)
                        {
                            case User::MAN :
                                $searchData->lookingFor[] = User::MAN;
                                break;
                            case User::WOMAN:
                                $searchData->lookingFor[] = User::WOMAN;
                                break;
                            case  User::BISEXUAL :
                                $searchData->lookingFor[] = User::BISEXUAL;
                                break;
                            default:
                                $searchData->lookingFor[] = $key;
                                break;
                        }
                    }
                    else{

                        // fro angular
                        switch($key)
                        {
                            case "man" :
                                $value ? $searchData->lookingFor[] = User::MAN : null;
                                break;
                            case "woman" :
                                $value ? $searchData->lookingFor[] = User::WOMAN : null;
                                break;
                            case "bisexual" :
                                $value ? $searchData->lookingFor[] = User::BISEXUAL : null;
                                break;
                            default:
                                $searchData->lookingFor[] = $key;
                                break;
                        }
                    }
                }
            }

            // check looking for, for old versions
            if(is_array($searchData->lookingFor)){
                $searchData->lookingFor = count($searchData->lookingFor) > 1 ? User::BISEXUAL : reset($searchData->lookingFor);
            }else{
                $searchData->lookingFor = $lookingFor;
            }

            // get interests
            $interests = array_key_exists('interests', $data) ? $data['interests'] : null;

            if(is_array($interests)){
                $searchData->interests = $interests;
            }
            else{
                $searchData->interests = json_decode($interests);
            }

            // set searching data
            $currentUser->setSearchingParams($searchData);
            $em->persist($currentUser);
            $em->flush();
        }

        $start = $request->get('start') ? (int)$request->get('start') : self::START;
        $count = $request->get('count') ? (int)$request->get('count') : self::COUNT;

        // get users
        $result = $em->getRepository('LBUserBundle:User')->findUserBySearchData($searchData, $currentUser,
            $start, $count, null, true);

        // get full name service
        $fullNameService = $this->get('app.full_name');

        $results = array();
        $cacheManager = $this->get('liip_imagine.cache.manager');
        $cnt = $result['cnt'];
        $users = $result['query'];


        // check user
        if($users){

            // loop for user
            foreach($users as $key => $usersArray){
                $user = $usersArray['user'];
                $user->usersCount = $cnt;

                // get path
                $path = $user->getProfileImagePath();
                $cacheVersion = $user->getProfileImageCacheVersion();

                // check has http in path
                if(strpos($path, 'http') === false){

                    try{
                        $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'members');
                        $srcPath = $cacheManager->getBrowserPath($path, 'members');
                        $user->imageCachePath = $srcPath . $cacheVersion;
                    }
                    catch(\Exception $e){
                        $user->imageCachePath = $path . $cacheVersion;
                    }
                }
                else{
                    $user->imageCachePath = $path . $cacheVersion;
                }

                $user->fullName =  $fullNameService->fullNameFilter($user);
                $user->status = $usersArray['status'];

                $results[] = $user;
            }
        }

        return  $results ;
    }

    /**
     * This function is used to get User`s that related to current user
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get User`s that related to current user",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     }
     * )
     *
     * @param $status
     * @return Response
     * @Rest\View(serializerGroups={"relatedUser"})
     * @deprecated
     */
    public function getRelatedAction($status = null)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get related users
        $relatedUsers = $em->getRepository('LBUserBundle:User')->getUsersRelations($currentUser, $status);

        return $relatedUsers;
    }

    /**
     * This function is used to get Update user position
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get Update user position",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     },
     * parameters={
     *      {"name"="longitude", "dataType"="float", "required"=true, "description"="User`s position longitude"},
     *      {"name"="latitude", "dataType"="float", "required"=true, "description"="User`s position latitude"},
     *
     * }
     * )
     *
     * @param Request $request
     * @return array
     * @Rest\View()
     */
    public function getGeoPositionAction(Request $request)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get push send service
        $pushNoteService = $this->container->get('app.push.note');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get lng
        $longitude = $request->get('longitude');

        // get lat
        $latitude = $request->get('latitude');

        // find geo with in radius
        $locations = $em->getRepository('AppBundle:Location')->findByCoordinates($longitude, $latitude);

        // check have user nearest ad
        if($locations){

            // loop for relation
            foreach($locations as $location){

                // check user ad relation
                $adUser = $em->getRepository('LBUserBundle:UserAdLocation')->findByUserAndLocation($currentUser, $location);

                // check user
                if(!$adUser){
                    // create new object, and insert location and user
                    $adUser = new UserAdLocation();
                    $adUser->setUser($currentUser);
                }

                $adUser->setLocation($location);
                $em->persist($adUser);
            }
            $em->flush();

            // find ad for friends with same radius
            $results = $em->getRepository("LBUserBundle:UserAdLocation")->findFriendsLocation($currentUser, $locations);

            // check results
            if($results){

                // loop for results
                foreach($results as $result){
                    // check push message
                    $push = $em->getRepository("LBUserBundle:UserPush")->findPush($currentUser, $result->getUser(), $result->getLocation());

                    // check push
                    if(count($push) == 0){

                        $secondUser = $result->getUser();
                        $push = new UserPush();
                        $push->setFirstUser($currentUser);
                        $push->setSecondUser($secondUser);
                        $push->setLocation($result->getLocation());
                        $em->persist($push);

                        $adGeo = $result->getLocation()->getAdGeo();


                        // generate for First user
                        $textFirstUser  = "You and " . $result->getUser()->getFirstName() . " are about  " . $adGeo->getRadius() . ' miles near ' . $adGeo->getName();
                        $messageFirstUser = array('adId' => $adGeo->getId(), 'message' => $textFirstUser);

                        // generate for Second user
                        $textSecondUser  = "You and " . $currentUser->getFirstName() . " are about  " . $adGeo->getRadius() . ' miles near ' . $adGeo->getName();
                        $messageSecondUser = array('adId' => $adGeo->getId(), 'message' => $textSecondUser);

                        try{
                            $em->flush();
                            $pushNoteService->sendPushNote($currentUser, $messageFirstUser);
                            $pushNoteService->sendPushNote($secondUser, $messageSecondUser);
                        }
                        catch(\Exception $e){};
                    }
                }
                return new JsonResponse(Response::HTTP_NO_CONTENT);
            }
        }
        else{
            $em->getRepository('LBUserBundle:UserAdLocation')->deleteExistLocations($currentUser);
            $em->getRepository('LBUserBundle:UserPush')->deleteExistPushes($currentUser);
        }

        return new JsonResponse(Response::HTTP_NO_CONTENT);
    }

    /**
     * This function is used to login user
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to login user",
     *  statusCodes={
     *         200="Returned when status changed",
     *         404="User not found"
     *     },
     * parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "description"="User`s username"},
     *      {"name"="password", "dataType"="password", "required"=true, "description"="User`s password"},
     *      {"name"="apikey",   "dataType"="string",   "required"=false, "description"="User`s apikey"}
     *
     * }
     *
     * )
     *
     * @Rest\View(serializerGroups={"for_mobile"})
     * @param $request
     * @return Response
     */
    public function postLoginAction(Request $request)
    {
        // get username from request
        $username = $request->get('username');

        // get password
        $password = $request->get('password');

        $userManager = $this->get('fos_user.user_manager');

        // get user from database
        $user = $userManager->findUserByUsernameOrEmail($username);

        // check user
        if($user){

            // get encoder service
            $encoderService = $this->get('security.encoder_factory');

            // get encoder
            $encoder = $encoderService->getEncoder($user);

            // is password valid
            if($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())){

                // check enabled
                if(!$user->isEnabled()){

                    // user not found
                    return new JsonResponse('User is disabled', Response::HTTP_FORBIDDEN);
                }

                // get session id
                $response = $this->loginAction($user, array('for_mobile'));

                // return result
                return $response;
            }
        }

        // user not found
        return new JsonResponse('Bad credentials', Response::HTTP_NOT_FOUND);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get user by action status(like, block, friend, favorite, visitor)",
     *  statusCodes={
     *         400="Returned when no such status code",
     *         401="Return when user not authorized",
     *     },
     *  parameters={
     *      {"name"="start", "dataType"="integer", "required"=false, "description"="start |by default 0"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="start |by default 20"}
     * }
     * )
     * @param $status
     * @return Response
     * @Rest\View(serializerGroups={"user_for_mobile_status"})
     */
    public function getUserByAction(Request $request, $status)
    {
        if(is_string($status)) {
            switch ($status) {
                case "like":
                    $status = UserRelation::LIKE;
                    break;
                case "friend":
                case "connections":
                    $status = UserRelation::FRIEND;
                    break;
                case "favorite":
                    $status = UserRelation::FAVORITE;
                    break;
                case "visitor":
                    $status = UserRelation::VISITOR;
                    break;
                case "block":
                    $status = UserRelation::BLOCK;
                    break;
                case "like_by_me":
                case "liked_by_me":
                    $status = UserRelation::LIKED_BY_ME;
                    break;
                case "favorite-by-my":
                case "favorited_by_me":
                    $status = UserRelation::FAVORITE_BY_ME;
                    break;
                default:
            }
        }

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get current user id
        $userId = $currentUser->getId();

        //check if status not exist or not integer
        if ($status != UserRelation::LIKE   &&
            $status != UserRelation::BLOCK  &&
            $status != UserRelation::FRIEND &&
            $status != UserRelation::VISITOR &&
            $status != UserRelation::FAVORITE &&
            $status != UserRelation::LIKED_BY_ME &&
            $status != UserRelation::FAVORITE_BY_ME
        ) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Wrong action");
        }

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $start = $request->get('start') ? (int)$request->get('start') : self::START;
        $count = $request->get('count') ? (int)$request->get('count') : self::COUNT;

        switch($status){
            case UserRelation::LIKE:
                $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findLikesUsersForMobile($currentUser, $start, $count);
                $users = $this->getUserFromRelations($userRelations, $currentUser, $status );
                $em->getRepository("LBUserBundle:UserRelation")->updateLikesUsers($currentUser, null, $userRelations);
                break;
            case UserRelation::FRIEND:
                $userRelations = $em->getRepository('LBUserBundle:UserRelation')->findFriendsForMobile($currentUser, $start, $count);
                $users = $this->getUserFromRelations($userRelations, $currentUser, $status );
                $em->getRepository("LBUserBundle:UserRelation")->updateLikesUsers($currentUser, null, $userRelations, false);
                break;
            case UserRelation::VISITOR:
                $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findVisitorUsersForMobile($userId, $start, $count);
                $users = $this->getUserFromRelations($userRelations, $currentUser, $status );
                $em->getRepository("LBUserBundle:UserRelation")->updateVisitorUsers($this->getUser(), null, $userRelations);
                break;
            case UserRelation::FAVORITE:
                $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findFavoriteUsersForMobile($userId, $start, $count);
                $users = $this->getUserFromRelations($userRelations, $currentUser, $status );
                $em->getRepository("LBUserBundle:UserRelation")->updateFavoriteUsers($this->getUser(), null, $userRelations);
                break;
            case UserRelation::LIKED_BY_ME:
                $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findLikeByMeUsersForMobile($currentUser, $start, $count);
                $users = $this->getUserFromRelations($userRelations, $currentUser, $status );
                break;
            case UserRelation::FAVORITE_BY_ME:
                $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findFavoriteByMeUsersForMobile($userId, $start, $count);
                $users = $this->getUserFromRelations($userRelations, $currentUser, $status );
                break;
            default:
                //get users list by action status
                $users = $em->getRepository('LBUserBundle:User')->findUserByAction($status, $userId, $start, $count);
                break;
        }

        // check users
        if($users){

            // get cache manager
            $cacheManager = $this->container->get('liip_imagine.cache.manager');

            // loop for users
            foreach($users as $user){

                // get path
                $path = $user->getProfileImagePath();
                $cacheVersion = $user->getProfileImageCacheVersion();

                // check profile image
                if($path){

                    // check has http in path
                    if(strpos($path, 'http') === false){

                        try{
                            $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'mobile_list');
                            $srcPath = $cacheManager->getBrowserPath($path, 'mobile_list');
                            $user->imageFromCache = $srcPath . $cacheVersion;

                            $this->get('liip_imagine.controller')->filterAction($this->get('request'), $path, 'box');
                            $srcPath = $cacheManager->getBrowserPath($path, 'box');
                            $user->imageCachePath = $srcPath . $cacheVersion;
                        }
                        catch(\Exception $e){
                            $user->imageFromCache = $path . $cacheVersion;
                            $user->imageCachePath = $path . $cacheVersion;
                        }
                    }
                    else{
                        $user->imageFromCache = $path . $cacheVersion;
                        $user->imageCachePath = $path . $cacheVersion;
                    }
                }
            }
        }

        return $users;
    }

    /**
     * @param $userRelations
     * @param User $user
     * @param $status
     * @return array
     */
    private function getUserFromRelations($userRelations, User $user, $status)
    {
        // empty data for result
        $users = array();
        $statusForMobile = false;

        // check user relation
        if($userRelations){

            // loop for relations
            foreach($userRelations as $userRelation){
                $fromUser = $userRelation->getFromUser();
                $toUser = $userRelation->getToUser();

                // check otherUser
                $otherUser = $fromUser->getId() == $user->getId() ? $toUser : $fromUser;

                if($status == UserRelation::LIKE){
                    $statusForMobile = $fromUser->getId() == $user->getId() ? !$userRelation->getIsLikeReadTo() : !$userRelation->getIsLikeReadFrom();
                }
                elseif($status == UserRelation::FAVORITE){

                    $favoriteStatus = $fromUser->getId() == $user->getId() ? $userRelation->getToFavoriteStatus() : $userRelation->getFromFavoriteStatus();
                    $statusForMobile = $favoriteStatus == UserRelation::NEW_FAVORITE ? true : false;

                }
                elseif($status == UserRelation::VISITOR){
                    $visitStatus = $fromUser->getId() == $user->getId() ? $userRelation->getToVisitorStatus() : $userRelation->getFromVisitorStatus();
                    $statusForMobile = $visitStatus == UserRelation::NEW_VISITOR ? true : false;

                }
                elseif($status == UserRelation::FRIEND){
                    $statusForMobile = $fromUser->getId() == $user->getId() ? !$userRelation->getIsLikeReadTo() : !$userRelation->getIsLikeReadFrom();
                }

                $otherUser->statusForMobile = $statusForMobile;

                $users[] = $otherUser;
            }
        }

        return $users;

    }


    /**
     * todo remove after mobile updates
     * @deprecated
     * @param $lookingFor
     * @return array|int|mixed
     */
    private function generateLookingFor($lookingFor)
    {
        // check if is not array try to decode
        if(!is_array($lookingFor)){
            $lookingFor = json_decode($lookingFor);
        }

        // if is array after decode
        if(is_array($lookingFor)){

            if(count($lookingFor) == 1){
                $lookingFor = reset($lookingFor);
            }else{
                $lookingFor = User::BISEXUAL;
            }
        }

        return $lookingFor;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to register new user",
     *  statusCodes={
     *         400="Bad request",
     *         204="There is no information to send back"
     *     },
     * parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "description"="User`s username"},
     *      {"name"="email", "dataType"="email", "required"=true, "description"="User`s email"},
     *      {"name"="plainPassword", "dataType"="string", "required"=true, "description"="User`s password"},
     *      {"name"="firstName", "dataType"="string", "required"=true, "description"="User`s first name | min=3 / max=20 symbols"},
     *      {"name"="lastName", "dataType"="string", "required"=true, "description"="User`s last name | min=3 / max=20 symbols"},
     *      {"name"="birthday", "dataType"="string", "required"=true, "description"="User`s birthday | in this 01/12/2015 format"},
     *      {"name"="city", "dataType"="string", "required"=true, "description"="User`s city | min=3 / max=100 symbols"},
     *      {"name"="zipCode", "dataType"="string", "required"=false, "description"="User`s zip code | max=10 symbols" },
     *      {"name"="summary", "dataType"="string", "required"=true, "description"="User`s summary | min=3 / max=500 symbols"},
     *      {"name"="craziestOutdoorAdventure", "dataType"="string", "required"=false, "description"="User`s craziest Outdoor Adventure " },
     *      {"name"="favoriteOutdoorActivity", "dataType"="string", "required"=false, "description"="User`s favorite Outdoor Activity" },
     *      {"name"="likeTryTomorrow", "dataType"="string", "required"=false, "description"="User`s like try tomorrow" },
     *      {"name"="iam", "dataType"="smallint", "required"=false, "description"="User`s  gender | MAN = 4 / WOMAN= 5" },
     *      {"name"="lookingFor", "dataType"="smallint", "required"=false, "description"="Hwo user looking for | MAN = 4 / WOMAN= 5" },
     *      {"name"="interests", "dataType"="array", "required"=false, "description"="ids of selected interest" },
     *      {"name"="facebook_id", "dataType"="string", "required"=false, "description"="Users facebook id " },
     *      {"name"="twitter_id", "dataType"="string", "required"=false, "description"="Users twitter id" },
     *      {"name"="instagram_id", "dataType"="string", "required"=false, "description"="Users instagram id" },
     *      {"name"="has_geo", "dataType"="boolean", "required"=false, "description"="Have user activate geo" },
     *      {"name"="feet", "dataType"="smallint", "required"=false, "description"="User height feet from 3 to 9" },
     *      {"name"="inches", "dataType"="smallint", "required"=false, "description"="User height feet from 0 to 12" },
     *      {"name"="profile_image", "dataType"="file", "required"=true, "description"="Users profile image file" },
     *      {"name"="apikey",   "dataType"="string",   "required"=false, "description"="User`s apikey"}
     *
     * }
     * )
     * @Rest\View(serializerGroups={"for_mobile", "user_for_mobile"})
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function postAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        $user = new User();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // set users data, if exist in request
        $user->setUsername(array_key_exists('username', $data) ? $data['username'] : null);
        $user->setEmail(array_key_exists('email', $data) ? $data['email'] : null);
        $user->setPlainPassword(array_key_exists('plainPassword', $data) ? $data['plainPassword'] : null);
        $user->setFirstName(array_key_exists('firstName', $data) ? $data['firstName'] : null);
        $user->setLastName(array_key_exists('lastName', $data) ? $data['lastName'] : null);
        $user->setBirthday(array_key_exists('birthday', $data) ? \DateTime::createFromFormat('d/m/Y', $data['birthday'])  : null);

        $city = array_key_exists('city', $data) ? $data['city'] : null;

        // for mobile
        if(!is_array($city) && $city){

            // set location
            $user->setLocation($city);
            $city = json_decode($city, true);
            $user->setCity(array_key_exists('address', $city) ? $city['address'] : null);
        }

//        $user->setSkyRide(array_key_exists('skiRide', $data) ? $data['skiRide'] : null);
        $user->setSummary(array_key_exists('summary', $data) ? $data['summary'] : null);

        $zipCode = array_key_exists('zipCode', $data) ? $data['zipCode'] : null;
        $user->setZipCode($zipCode);

        // check zip code
        if($zipCode){

            $lvService = $this->get('app.luvbyrd.service');
            $zipObject = $lvService->getZipObjByZipCode($zipCode);

            if($zipObject){
                $user->setZip($zipObject);
            }
        }


        $user->setCraziestOutdoorAdventure(array_key_exists('craziestOutdoorAdventure', $data) ? $data['craziestOutdoorAdventure'] : null);
        $user->setFavoriteOutdoorActivity(array_key_exists('favoriteOutdoorActivity', $data) ? $data['favoriteOutdoorActivity'] : null);
        $user->setLikeTryTomorrow(array_key_exists('likeTryTomorrow', $data) ? $data['likeTryTomorrow'] : null);
        $user->setIAm(array_key_exists('iam', $data) ? $data['iam'] : null);

        $user->setFeet(array_key_exists('feet', $data) ? $data['feet'] : null);
        $user->setInches(array_key_exists('inches', $data) ? $data['inches'] : null);
//        $user->setHeight(array_key_exists('height', $data) ? $data['height'] : null);
//        $user->setHeightUnit(array_key_exists('heightUnit', $data) ? $data['heightUnit'] : null);

        $user->setHasGeo(array_key_exists('has_geo', $data) ? $data['has_geo'] : null);

        $user->setFacebookId(array_key_exists('facebook_id', $data) ? $data['facebook_id'] : null);
        $user->setTwitterId(array_key_exists('twitter_id', $data) ? $data['twitter_id'] : null);
        $user->setInstagramId(array_key_exists('instagram_id', $data) ? $data['instagram_id'] : null);

        $lookingFor = array_key_exists('lookingFor', $data) ? $data['lookingFor'] : null;

        // todo remove after mobile update
        $lookingFor = $this->generateLookingFor($lookingFor);

//        if(!is_array($lookingFor)){
//            $lookingFor = json_decode($lookingFor);
//        }
        $user->setLookingFor($lookingFor);

        $user->setIAgree(true);

        $interests = array_key_exists('interests', $data) ? $data['interests'] : array();

        if(!is_array($interests)){
            $interests = json_decode($interests);
        }

        $interestsIds = array_map('trim', $interests);

        // get interests
        $objInterests = $em->getRepository("AppBundle:Interest")->findByIds($interestsIds);

        // set interest Errors
        $interestErrors = null;

        // get interests group from databases
        $interestGroups = $em->getRepository("AppBundle:InterestGroup")->findAllOrderByPosition();

        // check interest
        if($interestGroups){

            // loop for interest groups
            foreach($interestGroups as $interestGroup){

                // get interest
                $interest = $interestGroup->getInterest()->getValues();

                // check if is array
                $isInArray = array_intersect($objInterests, $interest);

                // check is selected from group
                if($isInArray && count($isInArray)){
                    continue;
                }
                else{
                    $interestErrors = "You must select at least one interest per interest group !";
                    break;
                }
            }
        }

        // check objects
        if($objInterests){

            // loop for objects
            foreach($objInterests as $objInterest){

                $user->addInterest($objInterest);
            }
        }

        // get validator
        $validator = $this->get('validator');

        $errors = $validator->validate($user, null, array('Registration'));

        // check count of errors
        if(count($errors) > 0 || $interestErrors){

            // returned value
            $returnResult = array();

            // check count
            if(count($errors) > 0){

                // loop for error
                foreach($errors as $error){
                    $returnResult[$error->getPropertyPath()] = $error->getMessage();
                }
            }

            // check interest result
            if($interestErrors){
                $returnResult['interests'] = $interestErrors;
            }

            // return json response
            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);
        }

        // get profile image
        $profileImage = $request->files->get('profile_image');

        // check profile image
        if(is_null($profileImage)){
            // return json response
            return new JsonResponse('Profile image can not be blank', Response::HTTP_BAD_REQUEST);
        }

        // check profile image
        if($profileImage){
            $file = new File();
            $file->setFile($profileImage);
            $file->setType(File::IMAGE);
            $user->setProfileImage($file);
            $em->persist($file);
            $file->setUser($user);
        }

        $user->setRegister(true);
        $em->persist($user);
        $em->flush();

        // get twig
        $emailTwig = $this->container->get('templating')->renderResponse('AppBundle:Blocks:welcomeEmail.html.twig');

        // send email
        $this->container->get("app.mandrill")->sendEmail($user->getEmail(), 'luvbyrd.com',
            'Registration in luvbyrd.com', $emailTwig->getContent());

        $this->container->get('app.luvbyrd.service')->sendMessageFromAdmin($user, 'Confirmation email',
            $this->generateMessageContent());

        $result = $this->loginAction($user, array('for_mobile', 'user_for_mobile'));

        return $result;
    }

    /**
     * @return string
     */
    private function generateMessageContent()
    {
        $text =   <<<EOT
      <div id="members-page">

        <h3 class="text-left">Welcome to LuvByrd</h3>

        <hr>

        <br />

        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <p>We’ve sent you an email to verify your account information.</p>
                <p>If you did not receive the email, please correct your email by going to "your profile" then "edit profile" and opening the "your account" tab.</p>
                <p>Please enjoy a BOGO deal on us. By one month, get one month free with the code, BOGO.</p>
            </div>
        </div>

    </div>
EOT;
//                <p>If you have any questions please feel free to email us anytime at <a href="mailto:info@luvbyrd.com">info@luvbyrd.com</a></p> <br>
        return $text;
    }

    /**
     * This function is used login by social data
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used login by social data",
     *  statusCodes={
     *         400="Returned when no such status code",
     *         204="There is no information to send back"
     *     },
     * requirements={
     *      {"name"="type", "dataType"="string", "requirement"=true, "description"="social type | twitter, facebook, instagram"},
     *      {"name"="accessToken", "dataType"="string", "requirement"=true, "description"="User`s social access_token"},
     *      {"name"="apikey", "dataType"="string", "required"=false, "description"="User`s apikey"}
     * }
     * )
     * @param $type
     * @param $accessToken
     * @return Response
     * @Rest\View(serializerGroups={"for_mobile"})
     */
    public function getSocialLoginAction($type, $accessToken)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $id = null;

        // switch for type
        switch($type){
            case "facebook":
                try{
                    $data = file_get_contents("https://graph.facebook.com/me?access_token=" . $accessToken);
                    $data = json_decode($data);
                    $id = $data->id;
                }
                catch(\Exception $e){
                    return new JsonResponse("Wrong access token", Response::HTTP_BAD_REQUEST);

                }
                break;
            case "instagram":
                try{
                    $data = file_get_contents("https://api.instagram.com/v1/users/self?access_token=" . $accessToken);
                    $data = json_decode($data);
                    $id = $data->data->id;
                }
                catch(\Exception $e){
                    return new JsonResponse("Wrong access token", Response::HTTP_BAD_REQUEST);

                }
                break;
            case "twitter":
                $data = explode('-', $accessToken);
                $id = is_array($data) ?  $data[0] : null;
                break;
            default:
                return new JsonResponse("Wrong type, type must be 'facebook', 'twitter', 'instagram'", Response::HTTP_BAD_REQUEST);
                break;
        }

        //get users list by action status
        $user = $em->getRepository('LBUserBundle:User')->findBySocial($type, $id);

        // check user
        if(!$user){
            return new JsonResponse('We have not this user in our database', Response::HTTP_NOT_FOUND);
        }

        // login user and get session id
        $result = $this->loginAction($user, array('for_mobile'));

        return $result;
    }


    /**
     * @param User $user
     * @param array $group
     * @return JsonResponse
     */
    private function loginAction(User $user, array $group)
    {
        // get firewall name
        $providerKey = $this->container->getParameter('fos_user.firewall_name');

        // check role
        if($this->isGranted("ROLE_ADMIN") OR $this->isGranted("SUPER_ADMIN")){
            $user->isAdmin = true;
        }
        else{
            $user->isAdmin = false;
        }

        // create new token
        $token = new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());

        //get request
        $request = $this->get('request_stack')->getCurrentRequest();

        //get em
        $em = $this->getDoctrine()->getManager();

        //get session
        $session = $this->get('session');

        //get json response
        $response = new JsonResponse();

        //get secret key
        $secretKey = $this->container->getParameter('secret');

        //check if api key exist in request
        if ($request->get('apikey')){
            //get api key value
            $apiKey = $user->getApiKey();

            if (is_null($apiKey)){
                //generate apiKye
                $apiKey = md5($user->getUsername() . $secretKey);

                //set api key for user
                $user->setApiKey($apiKey);
                $em->flush();
            }
        }
        //TODO: will be changed
        else {
            $this->get('security.token_storage')->setToken($token);
            $session->set($providerKey, serialize($token));
            $session->save();
        }

        //set user info in response
        $content['userInfo'] = $user;

        //check if apiKey exist
        if (isset($apiKey)){
            //set apiKey value in response
            $content['apiKey'] = $apiKey;
        }
        else {
            $cookie = $request->cookies;
            $phpSessionId = $cookie->get('PHPSESSID');

            if(!$phpSessionId){
                $phpSessionId = $session->getId();
            }
            $content['sessionId'] = $phpSessionId;
        }

        // create mail chimp user Data
        $mailchimpData = [
            'email'     => $user->getEmail(),
            'status'    => 'subscribed',
            'firstname' => $user->getFirstName(),
            'lastname'  => $user->getLastName(),
            'birthday'  => $user->getBirthday()->format('m/d/Y'),
            'zip_code'  => $user->getZipCode() ? $user->getZipCode() : ''
        ];

        // connect to mailchimp api service for create subscriber
        $this->container->get('app.mailchimp')->syncMailchimp($mailchimpData);

        $serializer = $this->get('serializer');

        //check if group exist
        if($group) {
            $contentJson = $serializer->serialize($content, 'json', SerializationContext::create()->setGroups($group));
        }
        else{
            $contentJson = $serializer->serialize($content, 'json', SerializationContext::create());
        }

        $response->setContent($contentJson);

        return $response;

    }

    /**
     * This function is used to get User`s that related to current user
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get User`s that related to current user",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     }
     * )
     *
     * @return Response
     * @Rest\View()
     */
    public function getActionCountAction()
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);
        $counts = $em->getRepository("LBUserBundle:UserRelation")->findAllUnread($currentUser, $blockUsers);

        return array('like_count' => $counts['likeCount'],
            'visitor_count' => $counts['visitorCount'],
            'favorite_count' => $counts['favoriteCount'],
            'messages_count' => $counts['messageCount'],
            'friends_count' => $counts['friendsCount']
        );
    }

    /**
     * This function is used to get User`s register id
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get User`s register id",
     *  statusCodes={
     *         204="There is no information to send back",
     *         401="Access allowed only for registered users",
     *         400="Bad request"
     *     },
     * parameters={
     *      {"name"="registrationId", "dataType"="string", "required"=true, "description"="Device Id"},
     *      {"name"="mobileOc", "dataType"="string", "required"=true, "description"="Mobile OC"},
     * }
     * )
     *
     * @return Response
     * @Rest\View()
     */
    public function putDeviceIdAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get all data
        $data = $request->request->all();

        // get register ids
        $registrationIds = array_key_exists('registrationId', $data) ? $data['registrationId'] : null;

        //get mobile OC
        $mobileOc = array_key_exists('mobileOc', $data) ? $data['mobileOc'] : null;

        if((!$registrationIds && !$mobileOc) || (!$registrationIds || !$mobileOc)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Empty parameters value");
        }

        // get exist registrations data
//        $regData = $currentUser->getRegistrationIds();

        $em->getRepository('LBUserBundle:User')->replaceRegistrationId($registrationIds);

//        // check is mobile device exist
//        if(is_array($regData) && array_key_exists($mobileOc, $regData)){
//
//            // get device array
//            $device = $regData[$mobileOc];
//
//            // check is registration in array
//            if(!in_array($registrationIds, $device)){
//
//                // push to array
//                array_push($device, $registrationIds);
//            }
//
//            // set mobile data
//            $regData[$mobileOc] = $device;
//
//        }
//        else{
//            // set mobile data
//            $regData[$mobileOc] =  $registrationIds;
//        }
        // set mobile data
        $regData[$mobileOc][] =  $registrationIds;

        // set register ids
        $currentUser->setRegistrationIds($regData);

        $em->persist($currentUser);
        $em->flush();

        return new JsonResponse(Response::HTTP_NO_CONTENT);
    }

    /**
     * This function is used to get send push not
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to get send push not",
     *  statusCodes={
     *         204="There is no information to send back",
     *         401="Access allowed only for registered users",
     *     }
     * )
     *
     * @return Response
     * @Rest\View()
     */
    public function getSendNoteAction($id = null)
    {
        $currentUser = null;

        if($id){
            $em = $this->get('doctrine')->getManager();
            $currentUser = $em->getRepository('LBUserBundle:User')->find($id);
        }

        if(!$currentUser){
            //get current user
            $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        }


        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        $message = array('adId' =>null, 'message' => "Test message");

        $this->get("app.push.note")->sendPushNote($currentUser, $message);
    }

    /**
     * This function is used to turn off user`s notification
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to turn off user`s notification",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     },
     * parameters={
     *      {"name"="switch", "dataType"="boolean", "required"=false, "description"="Turn on or of notification "},
     * }
     * )
     *
     * @param Request $request
     * @return Response
     * @Rest\View()
     */
    public function postNotificationSwitchAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get switcher
        $switch = $request->get('switch');

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // check status
        if($switch != null){
            // set current user status
            $currentUser->setNotificationSwitch((boolean)$switch);

            $em->persist($currentUser);
            $em->flush();
        }

        return $currentUser->getNotificationSwitch();
    }

    /**
     * This function is used to turn off user`s notification by type
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to turn off user`s notification by type",
     *  statusCodes={
     *         200="Returned when status changed",
     *         401="Access allowed only for registered users"
     *     },
     * parameters={
     *      {"name"="like_switch", "dataType"="boolean", "required"=false, "description"="Turn on or of like notification"},
     *      {"name"="favs_switch", "dataType"="boolean", "required"=false, "description"="Turn on or of favorits notification"},
     *      {"name"="messages_switch", "dataType"="boolean", "required"=false, "description"="Turn on or of messages notification"},
     *      {"name"="views_switch", "dataType"="boolean", "required"=false, "description"="Turn on or of messages notification"},
     * }
     * )
     *
     * @param Request $request
     * @return Response
     * @Rest\View()
     */
    public function postNotificationSwitchesByTypeAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get switcher
        $like_switch = $request->get('like_switch');
        $favs_switch = $request->get('favs_switch');
        $messages_switch = $request->get('messages_switch');
        $views_switch = $request->get('views_switch');

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        
        if($like_switch != null){
            // set current user status
            $currentUser->setNotificationLikeSwitch((boolean)$like_switch);
        }
        
        if($favs_switch != null){
            // set current user status
            $currentUser->setNotificationFavoriteSwitch((boolean)$favs_switch);
        }
        
        if($messages_switch != null){
            // set current user status
            $currentUser->setNotificationMessagesSwitch((boolean)$messages_switch);
        }
       
        if($views_switch != null){
            // set current user status
            $currentUser->setNotificationViewsSwitch((boolean)$views_switch);
        }
        

        if($like_switch != null || $favs_switch != null || $messages_switch != null || $views_switch != null){
            $em->persist($currentUser);
            $em->flush();
        }
        
        return $currentUser->getNotificationSwitch();
    }

    /**
     * This function is used to turn off user`s notification
     *
     * @ApiDoc(
     *  resource=true,
     *  section="User",
     *  description="This function is used to send messages to users",
     * )
     *
     * @param Request $request
     * @return Response
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @Rest\View()
     */
    public function putMessageFromAdminAction(Request $request)
    {
        // get user ids
        $ids = $request->get('userIds');
        $messageContent = $request->get('msg');

        // get entity manager
        $em = $this->getDoctrine()->getEntityManager();

        // find admin user
        $adminUser = $em->getRepository("LBUserBundle:User")->findOneBy(array('username' => 'admin'));

        if($adminUser){

            // get users by id
            $usersArray = $em->getRepository("LBUserBundle:User")->findUsersForSendingMessageById($ids, $adminUser->getId());

            // check user
            if($usersArray){

                $date = new \DateTime();
                $date = $date->format(\DateTime::ISO8601);

                // loop for user
                foreach ($usersArray as $array){

                    // check admin user
                    $user = $array['user']; // get user
                    $userRelation = $array['relation']; // get relation

                    // create new message
                    $message = new Message();
                    $message->setFromUser($adminUser);
                    $message->setToUser($user);
                    $message->setSubject('Message from admin');
                    $message->setContent($messageContent);
                    $message->setCreated($date);
                    $message->setIsDeleted(false);
                    $message->setIsRead(false);
                    $em->persist($message);

                    // check user relation
                    if(!$userRelation){

                        $userRelation = new UserRelation();
                        $userRelation->setFromUser($adminUser);
                        $userRelation->setToUser($user);
                        $em->persist($userRelation);
                    }

                }
                $em->flush();

                $request->getSession()->getFlashBag()->add("success", "Messages have been successfully sent");

                return new JsonResponse('', Response::HTTP_NO_CONTENT);

            }
        }

        return new JsonResponse('Users was not found', Response::HTTP_BAD_REQUEST);
    }

}