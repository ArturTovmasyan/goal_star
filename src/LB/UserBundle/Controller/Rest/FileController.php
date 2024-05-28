<?php

namespace LB\UserBundle\Controller\Rest;


use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Imagine\Imagick\Image;
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
 * @Rest\RouteResource("File")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class FileController extends FOSRestController
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to change file caption",
     *  statusCodes={
     *         200= "Ok",
     *         204="Return no content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * )
     * @param $request
     * @return array
     * @Rest\View()
     */
    public function postFromFbAction(Request $request)
    {
        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // get facebook id
        $facebookId = $request->get('facebookId', null);

        // get access token
        $accessToken = $request->get('accessToken', null);

        $result = array();

        // check is object
        if(!$facebookId && !$accessToken && is_object($user)){

            // get facebook id
            $facebookId = $user->getFacebookId();

            // get access token
            $accessToken = $user->getFacebookToken();

        }

        // get user service
        $userService = $this->get('lb.fb.service');

        // get fb images
        $albums = $userService->getFbAlbums($facebookId, $accessToken);

        $result['albums'] = $albums;

        if(is_object($user)){

            // get entity manager
            $em = $this->get('doctrine')->getManager();
            $selectedFiles = $em->getRepository("LBUserBundle:File")->findFileNamesByUser($user->getId());

            $result['selected'] = $selectedFiles;
        }


        return $result;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to change file caption",
     *  statusCodes={
     *         200= "Ok",
     *         204="Return no content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function postFbImageAction(Request $request)
    {

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user not found
        if(!is_object($user)) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "User not found");
        }


        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get fb images
        $fbImages = $request->get('fbImages');

        // get user service
        $this->get('lb.fb.service')->uploadFbImage($fbImages, $user);

        $em->persist($user);
        $em->flush();

        return new JsonResponse('', Response::HTTP_NO_CONTENT);

    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to change file caption",
     *  statusCodes={
     *         200= "Ok",
     *         204="Return no content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="filename", "dataType"="text", "required"=true, "description"="Users profile image filename" },
     *      {"name"="caption", "dataType"="text", "required"=true, "description"="File rotate degree" },
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postCaptionAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user not found
        if(!is_object($user)) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "User not found");
        }

        // get profile image
        $fileName = $request->get('filename');
        $caption = $request->get('caption');

        // check file
        if($fileName && $caption){

            // get file from database
            $file = $em->getRepository('LBUserBundle:File')->findOneBy(array('name' => $fileName));

            // check file
            if(!$file || $file->getUser()->getId() != $user->getId()){
                return new JsonResponse('File Not found', Response::HTTP_NOT_FOUND);
            }

            $file->setCaption($caption);
            $em->persist($file);
            $em->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        }
        else {
            return new Response('Empty post data', Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to rotate files",
     *  statusCodes={
     *         200= "Ok",
     *         204="Return no content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="filename", "dataType"="text", "required"=true, "description"="Users profile image filename" },
     *      {"name"="deg", "dataType"="integer", "required"=true, "description"="File rotate degree" },
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postRotateAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user not found
        if(!is_object($user)) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "User not found");
        }

        // get image id
        $fileId = $request->get('id');
        $deg = $request->get('deg');

        // check file
        if($fileId && $deg){

            // get file from database
            $file = $em->getRepository('LBUserBundle:File')->find($fileId);

            // check file
            if(!$file){
                return new JsonResponse('File NOt found', Response::HTTP_NOT_FOUND);
            }

            // get file upload link
            $path = $file->getAbsolutePath();

            try{
                $imagick = new \Imagick();
                $imagick->readImage($path);
                $imagick->rotateImage(new \ImagickPixel(), (int)$deg);

                //and save it on your server...
                file_put_contents($path, $imagick->getImage());
                $version = sha1(uniqid(mt_rand(), true));
                $version = mb_strcut($version, -10);
                $file->setCacheVersion($version);
                $em->persist($file);
                $em->flush();

                $this->get('app.luvbyrd.service')->removeCacheImage($file);

                return new Response(Response::HTTP_OK);
            }
            catch (\Exception $e){

                return new Response('Error with imagick ', Response::HTTP_FAILED_DEPENDENCY);

            }


        }
        else {
            return new Response('Empty post data', Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to upload files",
     *  statusCodes={
     *         200= "Ok",
     *         204="Return no content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="gallery_file", "dataType"="file", "required"=true, "description"="Users profile image file" },
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postUploadFileAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user not found
        if(!is_object($user)) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "User not found");
        }

        // get profile image
        $files = $request->files->get('gallery_file');

        // check file
        if($files){
            if(!is_array($files)) {
                $files = array($files);
            }

            foreach($files as $file) {

                // create file object
                $objFile = new File();

                // set file
                $objFile->setFile($file);

                $objFile->setType(File::IMAGE);

                // add file to object
                $user->addFile($objFile);

                $em->persist($objFile);
                $em->persist($user);
            }
            $em->flush();
            return new Response(Response::HTTP_OK);
        }
        else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Empty post data');
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to remove upload file by id",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Unauthorized user",
     *     }
     * )
     * @param $fileId
     * @return Response
     * @Rest\View()
     * @throws
     */
    public function deleteUploadFileAction($fileId)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check fileId
        if(!(int)$fileId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid file id parameter");
        }

        //get file by user id and file id
        $file = $em->getRepository('LBUserBundle:User')->findFileById($fileId);

        //check if file exist
        if($file) {
            $em->remove($file);

            // get next profile image
            $profileImage = $this->getNextProfileImage($file, $user);

            // check profile image
            if($profileImage){

                $user->setProfileImage($profileImage);
                $em->persist($profileImage);
                $em->persist($user);
            }

            $em->flush();
        }
        else{
            // return 404 if file not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "File not found");
        }

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to remove upload file by id",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Unauthorized user",
     *     }
     * )
     * @param $fileId
     * @return Response
     * @Rest\View()
     * @throws
     */
    public function deleteDropzoneFileAction($fileId)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //check fileId
        if(!(int)$fileId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid file id parameter");
        }

        //get file by user id and file id
        $file = $em->getRepository('LBUserBundle:User')->findFileById($fileId);

        //check if file exist
        if($file) {
            $em->remove($file);
            $em->flush();
        }
        else{
            // return 404 if file not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "File not found");
        }

        return new Response('', Response::HTTP_OK);
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
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to set profile images by id",
     *  statusCodes={
     *         200="Return when successful",
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Unauthorized user",
     *     }
     * )
     * @param $fileId
     * @return Response
     * @Rest\View()
     */
    public function postProfileImageAction($fileId)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user not found
        if(!is_object($user)) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "User not found");
        }

        //check fileId
        if(!(int)$fileId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid file id parameter");
        }

        //get file by user id and file id
        $file = $em->getRepository('LBUserBundle:User')->findFileById($fileId);

        //check if file exist
        if($file) {
            $user->setProfileImage($file);
            $em->persist($user);
            $em->flush();
        }
        else{
            // return 404 if file not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "File not found");
        }

        return new Response('', Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to get file by id",
     *  statusCodes={
     *         400="Bad request",
     *         404="Return when user not found",
     *         401="Unauthorized user",
     *     }
     * )
     * @return Response
     * @Rest\View(serializerGroups={"file"})
     */
    public function getAllFileAction()
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get file by user id and file id
        $file = $em->getRepository('LBUserBundle:User')->findFileByUserId($currentUser->getId());

        // get profileImage
        $profileImage = $currentUser->getProfileImage();

        // check is profile image exist
        if($profileImage){
            if(array_key_exists($profileImage->getId(), $file)){
                unset($file[$profileImage->getId()]);
            }

            array_unshift($file,$profileImage );

        }elseif($currentUser->getSocialPhotoLink()){

            $file[] = array('id'=> 0, 'web_path_for_mobile' => $currentUser->getSocialPhotoLink());
        }

        return is_array($file) ? array_values($file) : null;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  section="File",
     *  description="This function is used to change image file by canvas image",
     *  statusCodes={
     *         200= "Ok",
     *         204="Return no content",
     *         400="Bad request",
     *         401="Unauthorized user",
     *     },
     * parameters={
     *      {"name"="filename", "dataType"="text", "required"=true, "description"="Users image filename" },
     *      {"name"="imagePath", "dataType"="text", "required"=true, "description"="canvas File path in base64" },
     * }
     * )
     * @param $request
     * @return Response
     * @Rest\View()
     */
    public function postImageEditAction(Request $request)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        //get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //check if user not found
        if(!is_object($user)) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "User not found");
        }

        $fileName = $request->request->get('filename');
        $imagePath = $request->request->get('imagePath');

        if(!$fileName || !$imagePath){
            return new JsonResponse('Empty post data', Response::HTTP_BAD_REQUEST);
        }

        // generate base 64
        $croppedImage = str_replace('data:image/png;base64,', '', $imagePath);
        $croppedImage = str_replace(' ', '+', $croppedImage);

        // decode base 64
        $croppedImage = base64_decode($croppedImage);

        // get file from database
        $file = $em->getRepository('LBUserBundle:File')->findOneByFileNameAndUser($fileName, $user);

        // check file
        if(!$file || $file->getUser()->getId() != $user->getId()){
            return new JsonResponse('File Not found', Response::HTTP_NOT_FOUND);
        }

        // get file upload link
        $path = $file->getAbsolutePath();

        //and save it on your server...
        file_put_contents($path, $croppedImage);

        $version = sha1(uniqid(mt_rand(), true));
        $version = mb_strcut($version, -10);
        $file->setCacheVersion($version);
        $em->persist($file);
        $em->flush();

        // get li ip bundle configs
        $filterConfigurations = $this->get( 'liip_imagine.filter.configuration' );

        // get filters
        $filters = $filterConfigurations->all();
        // get filtar names
        $filters = array_keys($filters);

        // get cache manager
        $cacheManager = $this->get('liip_imagine.cache.manager');

        $cachePatch = $file->getUploadDir(). '/' . $file->getPath();

        // clear file from cache
        $cacheManager->remove($cachePatch, $filters);

        // get data manager
        $dataManager = $this->get('liip_imagine.data.manager');

        // get liip filter manager
        $filterManager = $this->get('liip_imagine.filter.manager');

        // get binary of filter
        $binary = $dataManager->find('gallery', $cachePatch);
        // cache images
        $cacheManager->store(
            $filterManager->applyFilter($binary, 'gallery'),
            $cachePatch,
            'gallery');

        $name = $cacheManager->getBrowserPath($file->getUploadDir(). '/' . $file->getPath(), 'gallery');
        $name = $name . $file->generateCacheVersion();

        return new JsonResponse(array('name' => $name), Response::HTTP_OK);

    }
}