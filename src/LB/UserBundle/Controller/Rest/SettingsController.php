<?php

namespace LB\UserBundle\Controller\Rest;


use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\Annotation\Groups;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Rest\RouteResource("Settings")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class SettingsController extends FOSRestController
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to change general setting",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Not authorized user",
     *     },
     * parameters={
     *      {"name"="currentPassword", "dataType"="string", "required"=true, "description"="User`s current password"},
     *      {"name"="changePassword", "dataType"="string", "required"=true, "description"="Users change password" },
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postGeneralAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        //get fos user manager
        $fosManager = $this->container->get("fos_user.user_manager");

        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get current password in post data
        $currentPassword = array_key_exists('currentPassword', $data) ? $data['currentPassword'] : null;

        //get change password in post data
        $changePassword = array_key_exists('changePassword', $data) ? $data['changePassword'] : null;

        if(!$currentPassword && (!$changePassword)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Post data es empty");
        }

        //get current user password
        $userPassword = $user->getPassword();

        //get encoder service
        $encoder_service = $this->get('security.encoder_factory');

        //encoder user
        $encoder = $encoder_service->getEncoder($user);

        //encoder sent current password
        $encode_data_pass = $encoder->encodePassword($currentPassword, $user->getSalt());

        if($userPassword == $encode_data_pass) {

            //set new password
            $user->setPlainPassword($changePassword);
            $fosManager->updateUser($user);

            return New Response(Response::HTTP_NO_CONTENT);
        }
        else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid current password");
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to disable user account",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Not authorized user",
     *     },
     * parameters={
     *      {"name"="disabled", "dataType"="string", "required"=true, "description"="User`s disabled data"},
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View(serializerGroups={"for_mobile"})
     */
    public function postDisableAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get disabled value in post data
        $disabled = array_key_exists('disabled', $data) ? $data['disabled'] : null;

        //check if disabled true
        if(!is_null($disabled)) {

            $user->setSearchVisibility($disabled);
            $em->persist($user);
            $em->flush();

            return $user;
        }
        else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Empty disabled data");
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to get user email settings",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Not authorized user",
     *     },
     * parameters={
     *      {"name"="emailSettings", "dataType"="array", "required"=false, "description"="User`s email settings data"},
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postEmailAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get disabled value in post data
        $settings = array_key_exists('emailSettings', $data) ? $data['emailSettings'] : null;

        if(!is_null($settings)) {

            // check is json
            if(!is_array($settings)){

                //decode settings data
                $settings = json_decode($settings);

                //convert settings stdClass to array
                $settings = (array) $settings;
            }

            $user->setEmailSettings($settings);
            $em->persist($user);
            $em->flush();

            return new Response(Response::HTTP_NO_CONTENT);
        }
        else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "empty emailSettings data");

        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to get user email settings",
     *  statusCodes={
     *         200="Return when successful",
     *         401="Not authorized user",
     *     }
     * )
     * @return Response
     * @Rest\View()
     */
    public function getEmailAction()
    {
        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get email settings
        $settings =$user->getEmailSettings();

        return $settings;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to get profile visibility settings",
     *  statusCodes={
     *         200="Return when successful",
     *         401="Not authorized user",
     *     }
     * )
     * @return Response
     * @Rest\View(serializerGroups={"profile_setting"})
     */
    public function getProfileVisibilityAction()
    {
        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        return $user;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to update profile visibility  settings",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         401="Not authorized user",
     *     },
     *  parameters={
     *      {"name"="state_visibility", "dataType"="smallint", "required"=true, "description"="User`s state  visibility | ONLY_ME = 0 / MY_FRIENDS = 1 /  ALL_MEMBERS = 2 / EVERYONE  = 3"},
     *      {"name"="zip_code_visibility", "dataType"="smallint", "required"=true, "description"="User`s zip code  visibility | ONLY_ME = 0 / MY_FRIENDS = 1 /  ALL_MEMBERS = 2 / EVERYONE  = 3" },
     *      {"name"="country_visibility", "dataType"="smallint", "required"=true, "description"="User`s zipCode visibility | ONLY_ME = 0 / MY_FRIENDS = 1 /  ALL_MEMBERS = 2 / EVERYONE  = 3" },
     *      {"name"="craziest_outdoor_adventure_visibility", "dataType"="smallint", "required"=true, "description"="User`s craziest Outdoor Adventure | ONLY_ME = 0 / MY_FRIENDS = 1 /  ALL_MEMBERS = 2 / EVERYONE  = 3" },
     *      {"name"="favorite_outdoor_activity_visibility", "dataType"="smallint", "required"=true, "description"="User`s favorite Outdoor Activity Visibility| ONLY_ME = 0 / MY_FRIENDS = 1 /  ALL_MEMBERS = 2 / EVERYONE  = 3" },
     *      {"name"="like_try_tomorrow_visibility", "dataType"="smallint", "required"=true, "description"="User`s like Try TomorrowVisibility| ONLY_ME = 0 / MY_FRIENDS = 1 /  ALL_MEMBERS = 2 / EVERYONE  = 3" },
     * }
     * )
     * @return Response
     * @param Request $request
     * @Rest\View(serializerGroups={"profile_setting"})
     */
    public function postProfileVisibilityAction(Request $request)
    {
        // get all data
//        $data = $request->request->all();

        // get entity manager
//        $em = $this->getDoctrine()->getManager();

        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($user)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

//        $user->setStateVisibility(array_key_exists('state_visibility', $data) ? $data['state_visibility'] : null);
//        $user->setZipCodeVisibility(array_key_exists('zip_code_visibility', $data) ? $data['zip_code_visibility'] : null);
//        $user->setCountryVisibility(array_key_exists('country_visibility', $data) ? $data['country_visibility'] : null);
//        $user->setCraziestOutdoorAdventureVisibility(array_key_exists('craziest_outdoor_adventure_visibility', $data) ? $data['craziest_outdoor_adventure_visibility'] : null);
//        $user->setFavoriteOutdoorActivityVisibility(array_key_exists('favorite_outdoor_activity_visibility', $data) ? $data['favorite_outdoor_activity_visibility'] : null);
//        $user->setLikeTryTomorrowVisibility(array_key_exists('like_try_tomorrow_visibility', $data) ? $data['like_try_tomorrow_visibility'] : null);

        // get validator
//        $validator = $this->get('validator');

//        $errors = $validator->validate($user, null, array('Edit'));

        // check count of errors
//        if(count($errors) > 0){
//
//            // returned value
//            $returnResult = array();
//
//            // loop for error
//            foreach($errors as $error){
//                $returnResult[$error->getPropertyPath()] = $error->getMessage();
//            }
//
//            // return json response
//            return new JsonResponse($returnResult, Response::HTTP_BAD_REQUEST);
//        }
//
//        $em->persist($user);
//        $em->flush();

        return new Response(Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="Settings",
     *  description="This function is used to change user pasword",
     *  statusCodes={
     *         204="No content",
     *         400="Bad request",
     *         404="Not found",
     *     },
     * parameters={
     *      {"name"="forgotPassword", "dataType"="string", "required"=true, "description"="User`s  forgot password"},
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postForgotPasswordAction(Request $request)
    {
        // get all data
        $data = $request->request->all();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get post data
        $forgotData = array_key_exists('forgotPassword', $data) ? $data['forgotPassword'] : false;

        //check if forgotData not exist
        if(!$forgotData) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Empty forgotPassword data");
        }

        //get user
        $user = $em->getRepository('LBUserBundle:User')->findUsersByEmailOrUsername($forgotData);

        //check if not logged in user
        if(!$user) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "User with $forgotData not found");
        }

        //generate token 
        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        //send confirm email
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new Response(Response::HTTP_NO_CONTENT);
    }
}