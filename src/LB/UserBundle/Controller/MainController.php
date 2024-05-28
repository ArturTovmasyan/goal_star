<?php

namespace LB\UserBundle\Controller;

use AppBundle\Model\EmailSettingsData;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\UserRelation;
use LB\UserBundle\Form\EmailSettingsType;
use LB\UserBundle\Form\ProfileAboutMeFormType;
use LB\UserBundle\Form\ProfileActivitiesFormType;
use LB\UserBundle\Form\ProfileBaseFormType;
use LB\UserBundle\Form\ProfileForms\AccountType;
use LB\UserBundle\Form\ProfileForms\BasicInfoType;
use LB\UserBundle\Form\ProfileForms\MyInterestsType;
use LB\UserBundle\Form\ProfileForms\PersonalInfoType;
use LB\UserBundle\Form\ProfileVisibilityFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MainController extends Controller
{
    /**
     * @Route("/profile11/{name}", defaults={"name" = "view"}, name="profile11")
     * @Security("has_role('ROLE_USER')")
     */
    public function indexAction($name)
    {
        return $this->render('LBUserBundle::layout.html.twig');
    }

    /**
     * this function return image edit modal template
     * 
     * @Route("/image/edit-modal", name="image_edit")
     * 
     */
    public function imageEditModalAction()
    {
        return $this->render('LBUserBundle:Main:imageEdit.html.twig');
    }

    /**
     * @Route("/me", name="profile_view_for_me")
     * @Route("/profile", name="fos_user_profile_show")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @return array
     */
    public function profileViewAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        $edit = $request->get('edit');

        // get user with relation
        $user = $em->getRepository('LBUserBundle:User')->findUserWithRelations($currentUser->getId());

        // get forms
        $basicForm = $this->createForm(new BasicInfoType(), $currentUser);

        // get forms
        $accountForm = $this->createForm(new AccountType(), $currentUser);

        // get forms
        $myInterestForm = $this->createForm(new MyInterestsType($this->container), $currentUser);

        // get forms
        $personalInfo = $this->createForm(new PersonalInfoType(), $currentUser);

        // get friends
//        $friendUsers = $em->getRepository('LBUserBundle:User')->getUsersFriend($currentUser->getId());


        // check request method
        if($request->isMethod("POST")){

            $request->get('lb_user_basic_info') ? $this->checkAndSaveData($basicForm, $request, $currentUser) :
                $request->get('lb_user_account') ? $this->checkAndSaveData($accountForm, $request, $currentUser) :
                    $request->get('lb_user_my_interests') ? $this->checkAndSaveData($myInterestForm, $request, $currentUser) :
                        $request->get('lb_user_personal_info') ? $this->checkAndSaveData($personalInfo, $request, $currentUser) : null;
        }


        return array(
            'user' => $user,
            'basicForm' => $basicForm->createView(),
            'accountForm' => $accountForm->createView(),
            'myInterestForm' => $myInterestForm->createView(),
            'personalInfo' => $personalInfo->createView(),
            'edit' => $edit,
        );
    }


    /**
     * @param $form
     * @param $request
     * @param $user
     */
    private function checkAndSaveData($form, $request, $user)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get old username
        $oldUsername =$form->getNormData()->getUsername();

        $form->handleRequest($request);

        // get new username
        $newUsername = $form->getNormData()->getUsername();

        if($form->isValid()){

            // check username s
            if($newUsername != $oldUsername) {

                $this->renameFolders($oldUsername, $newUsername, $user);
            }

            $em->persist($user);
            $em->flush();

        }
    }


    /**
     * @param $oldUsername
     * @param $newUsername
     * @param User $user
     */
    private function renameFolders($oldUsername, $newUsername, User $user)
    {
        // get all files
        $files = $user->getFiles();

        // check file
        if($files){

            // get one file to
            $firstFile = $files->first();

            $oldFolder = $firstFile->getUploadRootDir() . '/' . $oldUsername ;
            $newFolder  = $firstFile->getUploadRootDir() . '/' . $newUsername;

            if(is_dir($oldFolder)){

                if(!is_dir($newFolder)){
                    rename($oldFolder, $newFolder);
                }
                else{

                    // todo::change to
                    echo('some thing is wrong');
                    exit;
                }
            }

            // loop for all files
            foreach($files as $file){
                $oldPath = $file->getPath();
                $newPath = str_replace($oldUsername,  $newUsername ,$oldPath );
                $file->setPath($newPath);
            }
        }
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/profile/edit/about", name="profile_edit_about")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function editAboutMeAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(new ProfileAboutMeFormType(), $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('fos_user_profile_show');
        }

        return array('form' => $form->createView());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/profile/edit/activities", name="profile_edit_activities")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function editActivitiesAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(new ProfileActivitiesFormType($this->container), $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('fos_user_profile_show');
        }

        return array('form' => $form->createView());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/profile/edit/base", name="profile_edit_base")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function editBaseAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(new ProfileBaseFormType(), $user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('profile_edit_base');
        }

        return array('form' => $form->createView());
    }

    /**
     * @param $status
     * @return array
     *
     * @Route("/profile/friends/{status}", name="profile_friends")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function friendsAction($status)
    {
        $em = $this->getDoctrine()->getManager();
        $relatedUsers = $em->getRepository('LBUserBundle:User')->findRelatedUsersByStatus($status, $this->getUser()->getId());

        return array('relatedUsers' => $relatedUsers, 'status' => $status);
    }

    /**
     * @return array
     *
     * @Route("/profile/blocked", name="profile_blocked")
     * @Template("LBUserBundle:Main:blocked.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function blockedAction()
    {
        $em = $this->getDoctrine()->getManager();
        $relatedUsers = $em->getRepository('LBUserBundle:User')->findUserByAction(UserRelation::BLOCK, $this->getUser()->getId(), 0, 1000);

        return array('relatedUsers' => $relatedUsers, 'status' => UserRelation::BLOCK);
    }

    /**
     * This function is used to hide user from search
     *
     * @return array
     *
     * @Route("/profile/disable_account", name="disable_account")
     * @Template("LBUserBundle:Main:emailSettings.html.twig")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function disableAccountAction(Request $request)
    {
        //get user
        $user = $this->getUser();

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get checkbox value in request
        $checkboxValue = $request->request->get('disabled', false);

        //if user and checkbox value exist
//        if($user && $checkboxValue)
        {
            
            if($checkboxValue === false){
                $status = false;
                $text = 'enabled';
            }
            else{
                $status = true;
                $text = 'disabled';
            }

            $user->setSearchVisibility($status);
            $em->persist($user);
            $em->flush();

            // generate gender
            $gender = $user->getIAm() == User::WOMAN ? 'her' : 'his';

            // generate data
            $message = $user->getFirstName() . ' ' . $user->getLastName() . " $text  $gender account";

            // get mandrill
            $mandrill = $this->container->get("app.mandrill");

            //get from email in parameter
            $fromEmail = $this->container->getParameter('to_report_email');

            // send email via mandrill
            $mandrill->sendEmail($fromEmail, "Mike", 'luvbyrd', $message);

            // check if edited to deactivate redirect to members page
            if($user->getSearchVisibility() == false){
                return $this->redirectToRoute('members');
            }
        }
        return $this->redirectToRoute('email_settings');
    }

    /**
     * @return array
     *
     * @Route("/profile/visibility", name="profile_visibility")
     * @Template("LBUserBundle:Main:profileVisibility.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function profileVisibilityAction(Request $request)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->getUser();

        //get user id
        $groups = $em->getRepository('AppBundle:InterestGroup')->findGroupByUser($user->getId());

        //create form
        $form = $this->createForm(new ProfileVisibilityFormType(), $user);

        $form->handleRequest($request);

        //check if form valid
        if ($form->isValid()) {

            $em->flush();

            return $this->redirectToRoute('profile_visibility');
        }

        return array('form' => $form->createView(), 'groups' => $groups);

    }

    /**
     * @return array
     *
     * @Route("/profile/email_settings", name="email_settings")
     * @Template("LBUserBundle:Main:emailSettings.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function emailSettingsAction(Request $request)
    {

        //create email settings
        $emailSettings = new EmailSettingsData();

        //create form
        $form = $this->createForm(new EmailSettingsType($this->container), $emailSettings);

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        //check if form valid
        if ($form->isValid()) {

            $em->flush();

            return $this->redirectToRoute('email_settings');
        }

        return array('form' => $form->createView());

    }

    /**
     * @return array
     *
     * @Route("/profile/gallery", name="gallery")
     * @Template("LBUserBundle:Main:gallery.html.twig")
     * @Security("has_role('ROLE_USER')")
     */
    public function profileGalleryAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        // check post method
        if($request->isMethod("POST")){

            // get file
            $files = $request->get('gallery_file');

            // check file
            if($files){

                // get json from request
                $files = json_decode($files);

                if(count($files) > 1) {
                    // remove duplicate
                    $files = array_unique($files);
                }

                // get files form bd
                $objFiles = $em->getRepository('LBUserBundle:File')->findByIDs($files);

                // check files
                if($objFiles){

                    // loop for goal images
                    foreach($objFiles as $objFile){

                        // add to user
                        $currentUser->addFile($objFile);

                        $em->persist($objFile);
                    }

                    $em->flush();
                }
            }
        }

        // get files
        $files = $currentUser->getFiles();

        return array('files' => $files);
    }

    /**
     * @return array
     *
     * @Route("/file-delete/{id}", name="file-delete")
     * @Security("has_role('ROLE_USER')")
     * @ParamConverter("file", class="LBUserBundle:File")
     * @param File $file
     */
    public function deleteAction(File $file)
    {

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // check user
        if($this->get('security.authorization_checker')->isGranted("ROLE_SUPER_ADMIN")){

            // get owner of file
            $currentUser = $file->getUser();

            $em->remove($file);
        }
        else{

            // get current user
            $currentUser = $this->getUser();

            // get users all files
            $userFiles = $currentUser->getFiles();

            // check user files
            if($userFiles){

                // have user this file
                if($userFiles->contains($file)){

                    // remove file from user
                    $currentUser->removeFile($file);
                    $em->remove($file);
                }
            }
        }

        // get next profile image
        $profileImage = $this->getNextProfileImage($file, $currentUser);

        // check profile image
        if($profileImage){

            $currentUser->setProfileImage($profileImage);
            $em->persist($profileImage);
        }

        $em->flush();

        $url = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '/';

        return $this->redirect($url);
    }

    /**
     * @param $file
     * @param $user
     * @return null
     */
    private function getNextProfileImage($file, &$user)
    {
        // if profile image
        $profileImage = null;

        // check is deleted file profile image
        if(($file->getId() == ($user->getProfileImage() ?
                $user->getProfileImage()->getId() : null)) || $user->getProfileImage() == null ){

            // get next image
            $profileImage = $user->getNextImage($file);
        }

        return $profileImage;
    }


    /**
     * This action is used for upload images from drag and drop
     *
     * @Route("/add-images", name="add_images")
     * @Method({"POST"})
     * @param Request $request
     * @return array
     */
    public function addImagesAction(Request $request)
    {
        // get all files form request
        $image = $request->files->get('file');

        // get current user
        $currentUser = $this->getUser();

        // check file
        if($image){

            // get validator
            $validator = $this->get('validator');

            // get entity manager
            $em = $this->getDoctrine()->getManager();

            // create new file object
            $file = new File();

            // set file
            $file->setFile($image);

            // set type
            $file->setType(File::IMAGE);

            // validate goal image
            $error = $validator->validate($file);

            // check count
            if(count($error) > 0){
                return new JsonResponse($error[0]->getMessage(), Response::HTTP_BAD_REQUEST);

            }

            // if current user
            if($currentUser instanceof User){
                $currentUser->addFile($file);
                $em->persist($currentUser);
            }

            // persist
            $em->persist($file);

            // flush data
            $em->flush();
            return new JsonResponse($file->getId(), Response::HTTP_OK);

        }

        return new JsonResponse('', Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/set-profile/{id}", name="set_profile")
     * @param Request $request
     * @ParamConverter("file", class="LBUserBundle:File")
     * @Security("has_role('ROLE_USER')")
     * @return array
     * @param File $file
     */
    public function setProfileAction(Request $request, File $file)
    {
        // get current user
        $currentUser = $this->getUser();

        // set profile image
        $currentUser->setProfileImage($file);

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        $em->persist($currentUser);

        $em->flush();

        $url = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '/';

        return $this->redirect($url);
    }

    /**
     * @Route("/activate-deactivate/{id}", name="activate_deactivate")
     * @Security("has_role('ROLE_ADMIN')")
     * @return array
     * @param $id
     */
    public function activateDeactivateAction($id)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get filters
        $filters =$em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;

        // get user
        $user = $em->getRepository("LBUserBundle:User")->find($id);

        // check user
        if(!$user){
            throw $this->createNotFoundException('user not found');
        }

        // set activate |or deactivate
        $user->setDeactivate($user->getDeactivate() === true ? false : true);

        $em->persist($user);

        $em->flush();

        $url = array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '/';

        return $this->redirect($url);
    }
}
