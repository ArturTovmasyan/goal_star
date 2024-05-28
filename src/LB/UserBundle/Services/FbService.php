<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/12/16
 * Time: 6:49 PM
 */

namespace LB\UserBundle\Services;

use Doctrine\ORM\EntityManager;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;


/**
 * Class FbService
 * @package LB\UserBundle\Services
 */
class FbService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var
     */
    private $clientId;

    /**
     * @var
     */
    private $secretId;

    /**
     * FbService constructor.
     * @param EntityManager $em
     * @param $clientId
     * @param $secretId
     */
    public function __construct(EntityManager $em, $clientId, $secretId)
    {
        $this->em = $em;
        $this->clientId = $clientId;
        $this->secretId = $secretId;
    }


    /**
     * @param $selectedFbImages
     * @param User $user
     */
    public function uploadFbImage($selectedFbImages, User &$user)
    {
        // check images from facebook
        if(is_array($selectedFbImages)){

            // loop for images
            foreach ($selectedFbImages as $fbImage){

                // check file name
                $file = new File();

                // create new image file
                $file->setType(File::IMAGE);

                // get image info
                $info = pathinfo($fbImage);

                // get file original name
                $originalName = $info['filename'];

                // get file info
                $ext = isset($info['extension']) ? substr($info['extension'], 0,3) : 'jpg';

                // generate name for profile image
                $fileName = sha1(uniqid(mt_rand(), true)) . '.' .$ext;

                // get uploaded dir
                $dir = $file->getDir() ;

                // check is exist folder, and create if not exit
                if(!file_exists($dir)){
                    mkdir($dir, 0777, true);
                }

                // get file dir
                $fileDir = $dir . '/' . $fileName;

                // get image
                $image = file_get_contents($fbImage);

                // put image into folder
                file_put_contents($fileDir, $image);

                // set name, original name, and path to image
                $file->setName($fileName);
                $file->setClientName($originalName);
                $file->setPath($file->getPathForUploadPath());

                // add to user
                $user->addFile($file);

                $this->em->persist($file);

            }
        }
    }

    /**
     * @param $facebookId
     * @param $accessToken
     * @return array
     */
    public function getFbAlbums($facebookId, $accessToken)
    {
        // data for albums
        $albums = array();

        // generate url
        $urlAlbums = "https://graph.facebook.com/$facebookId/albums?access_token=$accessToken";

        // get albums
        $fbAlbums = $this->getAlbums($urlAlbums);

        // check fb albums
        if(is_array($fbAlbums )){

            // loop for albums
            foreach ($fbAlbums as $fbAlbum){

                $data['name'] =  $fbAlbum['name'];
                $data['id'] =  $fbAlbum['id'];

                // cover link image
                $coverLink = "https://graph.facebook.com/{$fbAlbum['id']}?fields=picture&access_token=$accessToken";

                // get images
                $coverImage =  file_get_contents($coverLink);

                // decode image
                $coverImage = json_decode($coverImage, true);

                // check cover image array
                if(is_array($coverImage) && array_key_exists('picture', $coverImage)){

                    // get cover image
                    $coverImage = $coverImage['picture'];
                    $coverImage = $coverImage['data'];
                    $coverImage = $coverImage['url'];
                    $data['cover'] =  $coverImage;
                }

                // get photo link
                $photoLink = "https://graph.facebook.com/{$fbAlbum['id']}/photos/?fields=source&access_token=$accessToken";

                // get images
                $images = $this->getImages($photoLink);

                $data['images'] =  $images;

                $albums[] = $data;
            }
        }

        return $albums;
    }

    /**
     * @param $url
     * @return array
     */
    private function getImages($url)
    {
        // get images
        $images =  file_get_contents($url);

        // decode image
        $images = json_decode($images, true);

        // empty source
        $sources = array();

        // check image and data
        if(is_array($images) && array_key_exists('data', $images)){

            $data = $images['data'];

            if(count($data) > 0){
                // loop for images
                foreach ($data as $image){
                    $sources[] = $image['source'];
                }

                $paging = $images['paging'];
                if(array_key_exists('next', $paging)){
                    $url = $paging['next'];

                    $nextImages = $this->getImages($url);
                    $sources = array_merge($sources, $nextImages);
                }
            }
        }

        return $sources;
    }

    /**
     * @param $url
     * @return array
     */
    private function getAlbums($url)
    {
        // get albums
        $albums =  file_get_contents($url);

        // decode albums
        $albums = json_decode($albums, true);

        // empty source
        $sources = array();

        // check image and data
        if(is_array($albums) && array_key_exists('data', $albums)){

            $data = $albums['data'];

            // loop for images
            foreach ($data as $album){
                $sources[] = $album;
            }

            $paging = $albums['paging'];
            if(array_key_exists('next', $paging)){
                $url = $paging['next'];

                $nextImages = $this->getAlbums($url);
                $sources = array_merge($sources, $nextImages);
            }
        }

        return $sources;
    }
}