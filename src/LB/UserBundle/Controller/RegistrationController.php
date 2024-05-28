<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/6/15
 * Time: 6:19 PM
 */

namespace LB\UserBundle\Controller;

use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Form\RegistrationType2;
use LB\UserBundle\Form\RegistrationType3;
use LB\UserBundle\Provider\FOSUBUserProvider;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class RegistrationController
 * @package LB\UserBundle\Controller
 */
class RegistrationController extends BaseController
{

//    /**
//     * @return array
//     *
//     * @Route("/share", name="share")
//     * @Template()
//     */
//    public function shareAction()
//    {
//        $oneAllService = $this->container->get('lb.one_all.service');
//
//        $t = $oneAllService->shareContent();
//
//        return array();
//    }


    /**
     * @return RedirectResponse
     */
    public function registerAction()
    {
//        // check if is logged
//        if(is_object($user = $this->container->get('security.token_storage')->getToken()->getUser())){
//            throw new AccessDeniedHttpException('You have not access to this page');
//
//        }

        //get request
        $request = $this->container->get('request');

        // empty data for used variables
        $captchaError = null; // for captcha error
        $profileImageError = null; // for profile image error
//        $croppedImage = null; // for cropped images
        $galleryError = null;
        $objFiles  = array(); // for images id`s from dropzone
        $selectedFbImages = $request->get('fbImages', "[]"); // get fb images

        // fos users process
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        // get logger
        $logger = $this->container->get('monolog.logger.register');

        // get session
        $session = $request->getSession();

        // get user from session (social user save in session, after success redirect from social)
        $currentUser = $session->get(User::SESSION_NAME);

        // get referer
        $referrer = $request->headers->get('referer');

        // get from session, is from social value
        $isFromSocial = $session->get(FOSUBUserProvider::IS_FROM_SOCIAL);
        $registerUrl = $this->container->get('router')->generate('fos_user_registration_register');
        $registerUrl = $request->getSchemeAndHttpHost() . $registerUrl;

        // check if data is set
        if($isFromSocial){
            $session->remove(FOSUBUserProvider::IS_FROM_SOCIAL);

            $image = $currentUser->getSocialPhotoLink();
            $currentUser->setSocialPhotoLink(null);

            // check file name
            $profileImage = new File();

            // create new image file
            $profileImage->setType(File::IMAGE);

            // get image info
            $info = pathinfo($image);

            $originalName = 'profileImage';

            // get file info
            $ext = isset($info['extension']) ? substr($info['extension'], 0,3) : 'jpg';

            // generate name for profile image
            $fileName = sha1(uniqid(mt_rand(), true)) . '.' .$ext;

            // get uploaded dir
            $dir = $profileImage->getDir() ;

            // check is exist folder, and create if not exit
            if(!file_exists($dir)){
                mkdir($dir, 0777, true);
            }

            // get file dir
            $fileDir = $dir . '/' . $fileName;

            // get image
            $objImage = file_get_contents($image);

            // put image into folder
            file_put_contents($fileDir, $objImage);

            // set name, original name, and path to image
            $profileImage->setName($fileName);
            $profileImage->setClientName($originalName);
            $profileImage->setPath($profileImage->getPathForUploadPath());

            $this->container->get('doctrine')->getManager()->persist($profileImage);
            $this->container->get('doctrine')->getManager()->flush($profileImage);
            $objFiles[] = $profileImage;
        }

        // check twitter id, from twitter id referrer is null
        if(!$isFromSocial &&  $referrer != $registerUrl){
            $session->remove(User::SESSION_NAME);
            $currentUser = null;
        }

        // get register
        $registerForm = $request->get('fos_user_registration_form');

        // check if current user us exist (this is need for social user)
        $form->setData(is_object($currentUser) ? $currentUser : null);

        // if is not register, get data and set in to form (this is need, if user comes from homepage, and already fill in register data )
        // or come from social , and has some fields from social
        if(!$registerForm){

            // from social
            if($currentUser){
                $form->setData($currentUser);
            }
            else{ // from homepage
               // set username email and agreement from homepage form
                $username = $request->get('_username');
                $form->get('username')->setData($username);
                $email = $request->get('_email');
                $form->get('email')->setData($email);

                $agree = $request->get('_agree');

                $form->get('iAgree')->setData($agree ? true : false);
            }
        }

        else{// standard registration

//            // get file
//            $originFile = $request->files->get('originFile');
//
//            // get cropped images
//            $croppedImage = $request->get('cropped_image');
//
//            // check is images cropped
//            $croppedImage = $croppedImage || strlen($croppedImage) > 2 ? $croppedImage : null;;

//            // check file
//            if(is_null($originFile) && !$croppedImage &&(!$currentUser || ($currentUser && !$currentUser->getSocialPhotoLink())) ){
//
//                //set profile image error
//                $profileImageError = 'Profile image can not be blank';
//
//                // add email
//                $logger->error($form->get('email')->getData());
//
//                // add log
//                $logger->error( $form->get('email')->getData() . ' : -  Profile image can not be blank');
//            }

            // get entity manager
            $em = $this->container->get('doctrine')->getManager();

            // check process
            $process = $formHandler->process($confirmationEnabled, $currentUser, $profileImageError, $reg = true);

            // get environment
            $env = $this->container->getParameter("kernel.environment");

            // get file
            $files = $request->get('gallery_file');

            // check file
            if($files) {

                // get json from request
                $files = json_decode($files);

                if($files && is_array($files)){

                    // remove duplicate
                    $files = array_unique($files);

                    // get files form bd
                    $objFiles = $em->getRepository('LBUserBundle:File')->findByIDs($files);
                }
            }

            if(count($objFiles) == 0){
                $galleryError = 'Gallery can not be blank';
            }

            if($env == 'prod'){
                $captcha = $request->get('g-recaptcha-response');

                $captchaResult = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfenxITAAAAAJMxXag1h30YUUgpK4IcH6SIGM4Y&response=".$captcha);

                $captchaResult = json_decode($captchaResult);

                if(!$captchaResult->success){
                    $captchaError = 'Please verify that you are not a robot.';

                    // add log
                    $logger->error( $form->get('email')->getData() . ' : -  Please verify that you are not a robot.');
                }
            }

            // check process, and if env is prod, check captcha result(captcha does not work in test and dev envairmont)
            if ($process  && ($env != 'prod' || (isset($captchaResult) && $captchaResult->success)) && !$galleryError) {

                // get data from user
                $user = $form->getData();
//
//                // check cropped images
//                if($croppedImage){
//
//                    // create new image file
//                    $profileImage = new File();
//                    $profileImage->setType(File::IMAGE);
//
//                    // generate base 64
//                    $croppedImage = str_replace('data:image/png;base64,', '', $croppedImage);
//                    $croppedImage = str_replace(' ', '+', $croppedImage);
//
//                    // decode base 64
//                    $croppedImage = base64_decode($croppedImage);
//
//                    // generate name for profile image
//                    $fileName = sha1(uniqid(mt_rand(), true)) . '.jpg';
//
//                    // get uploaded dir
//                    $dir = $profileImage->getDir() ;
//
//                    // check is exist folder, and create if not exit
//                    if(!file_exists($dir)){
//                        mkdir($dir, 0777, true);
//                    }
//
//                    $fileDir = $dir . '/' . $fileName;
//
//                    // put image into folder
//                    file_put_contents($fileDir, $croppedImage);
//
//                    // check file
//                    if(!file_exists($fileDir) || !is_file($fileDir)){
//
//                        //set profile image error
//                        $profileImageError = 'Something wnt wrong with profile image, please upload again';
//                    }
//
//                    // set name, original name, and path to image
//                    $profileImage->setName($fileName);
//                    $profileImage->setClientName('profileImage.jpg');
//                    $profileImage->setPath($profileImage->getPathForUploadPath());
//
//                    $user->setProfileImage($profileImage);
//                    $profileImage->setUser($user);
//                }


                // remove old images, older than two hours
//                $this->removeAllOldImages();

                // check files ids from dropzone
                if($objFiles) {

                    // loop for goal images
                    foreach($objFiles as $key => $objFile){

                        if($key == 0){
                            $user->setProfileImage($objFile);
                            $objFile->setUser($user);
                        }
                        else{
                            // add to user
                            $user->addFile($objFile);
                        }

                        $em->persist($objFile);
                    }
                }

                // set register, client ip
                $user->setRegister(true);
                $ip = $request->getClientIp();
                $user->setIPAddress($ip);
                $user->setStep(User::FIRST);

                // check profile image error
                if(!$profileImageError){

                    $selectedFbImages = $selectedFbImages ? json_decode($selectedFbImages) : [];

                    // get user service
                    $this->container->get('lb.fb.service')->uploadFbImage($selectedFbImages, $user);

                    $zipCode = $user->getZipCode();

                    // check zip code
                    if($zipCode){

                        $lvService = $this->container->get('app.luvbyrd.service');
                        $zipObject = $lvService->getZipObjByZipCode($zipCode);

                        if($zipObject){
                            $user->setZip($zipObject);
                        }
                    }

                    // persist data
                    $em->persist($user);
                    $em->flush();

                    if($user) // send to mail chimp
                    {
                        // create mailchimp user Data
                        $mailchimpData = [
                            'email'     => $user->getEmail(),
                            'status'    => 'subscribed',
                            'firstname' => $user->getFirstName(),
                            'lastname'  => $user->getLastName(),
                            'birthday'  => $user->getBirthday() ? $user->getBirthday()->format('m/d/Y') : null,
                            'zip_code'  => $user->getZipCode() ? $user->getZipCode() : ''
                        ];

                        // connect to mailchimp api service for create subscriber
                        $this->container->get('app.mailchimp')->syncMailchimp($mailchimpData);
                    }

                    // remove user from session
                    $session->remove(User::SESSION_NAME);

                    $authUser = false;
                    if ($confirmationEnabled) {
                        $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
                        $route = 'fos_user_registration_check_email';
                    } else {
                        $authUser = true;
                        $route = 'members';
                    }

                    // continue fos user registration process
                    $this->setFlash('fos_user_success', 'registration.flash.user_created');
                    $url = $this->container->get('router')->generate($route);

                    $response = new RedirectResponse($url);

                    if ($authUser) {
                        $this->authenticateUser($user, $response);
                    }

                    // get twig
                    $emailTwig = $this->container->get('templating')->renderResponse('AppBundle:Blocks:welcomeEmail.html.twig');

                    // send email
                    $this->container->get("app.mandrill")->sendEmail($user->getEmail(), 'luvbyrd.com',
                        'Registration in luvbyrd.com', $emailTwig->getContent());

                    $this->container->get('app.luvbyrd.service')->sendMessageFromAdmin($user, 'Confirmation email',
                        $this->generateMessageContent());

                    return $response;
                }

            }
            else{

                // get form errors
                $errors = $form->getErrors(true);

                // add email
                $logger->error($form->get('email')->getData());

                foreach($errors as $error){

                    $data = $error->getOrigin()->getData();

                    if($error->getCause() && !is_object($error->getCause()->getInvalidValue())){
                        $data = $error->getCause()->getInvalidValue();
                    }

                    if(is_array($data)){

                        $data = reset($data) . ' / ' . end($data);
                    }

                    // add log
                    $logger->error( $data .  ' : - ' . $error->getMessage());
                }

            }
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
            'captchaError' => $captchaError,
            'currentUser' => $currentUser,
            'profileImageError' => $profileImageError,
            'galleryError' => $galleryError,
//            'croppedImage' => $croppedImage,
            'objFiles' => $objFiles, 'selectedFbImages' => $selectedFbImages
        ));
    }

    /**
     * @Route("/register-step-2", name="register_step_2")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function registerStep2Action(Request $request)
    {

        // check if is logged
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //set form
        $form = $this->container->get('form.factory')->create(new RegistrationType2(), $user);

        // check method
        if($request->isMethod("POST")) {

            $form->handleRequest($request);

            //check if form data valid
            if ($form->isValid()) {

                $user->setStep(User::SECOND);
                $em->persist($user);
                $em->flush();

                return new Response(!is_null($request->get('continue')) ? 'continue' : 'skip');

            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/register-step-3", name="register_step_3")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function registerStep3Action(Request $request)
    {
        // check if is logged
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        //set form
        $form = $this->container->get('form.factory')->create(new RegistrationType3($this->container), $user);

        // check method
        if($request->isMethod("POST")) {

            $form->handleRequest($request);

            // get interests group from databases
            $interestGroups = $em->getRepository("AppBundle:InterestGroup")->findAllOrderByPosition();

            // get selected interest from form
            $selectedInterests = $form->get('interests')->getData();

            // get error from form
            $interestError = $form->get('interests')->getErrors()->count();

            // check interest
            if($interestGroups && $interestError == 0){

                // loop for interest groups
                foreach($interestGroups as $interestGroup){

                    // get interest
                    $interest = $interestGroup->getInterest()->getValues();

                    // check if is array
                    $isInArray = array_intersect($selectedInterests, $interest);

                    // check is selected from group
                    if($isInArray && count($isInArray)){
                        continue;
                    }
                    else{

                        // if error is exist, add it ot form and break, and set process to false
                        $error = new FormError("You must select at least one interest per interest group !");
                        $form->get('interests')->addError($error);
                        break;
                    }
                }
            }

            //check if form data valid
            if ($form->isValid()) {


                $zipCode = $user->getZipCode();

                // check zip code
                if ($zipCode) {

                    $lvService = $this->container->get('app.luvbyrd.service');
                    $zipObject = $lvService->getZipObjByZipCode($zipCode);

                    if ($zipObject) {
                        $user->setZip($zipObject);
                    }
                }

                $user->setStep(User::THIRD);
                $em->persist($user);
                $em->flush();

                return new Response('ok');
            }
        }

        return array('form' => $form->createView());
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

        return $text;
    }


    /**
     *  This function is used to remove files and goal images from db
     */
    private function removeAllOldImages()
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // get all old images
        $files = $em->getRepository('LBUserBundle:File')->findAllOlder();

        // loop for images
        if($files){

            // loop for images
            foreach($files as $file){
                $em->remove($file);
            }
        }
    }

}