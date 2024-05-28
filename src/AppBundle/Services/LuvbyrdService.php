<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 1/22/16
 * Time: 4:34 PM
 */

namespace AppBundle\Services;

use AppBundle\Entity\InterestGroup;
use LB\MessageBundle\Entity\Message;
use LB\UserBundle\Entity\User;
use LB\UserBundle\Entity\ZipCode;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\UserRelation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class LuvbyrdService
 * @package AppBundle\Services
 */
class LuvbyrdService
{
    const VIEW   = 'view';
    static $STEP_2_ACTION = array('member');
    static $STEP_3_ACTION = array('users_like', 'like_by_me', 'users_connections',
        'visitor', 'favorite', 'favorite-by-my', 'message_users');

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected  $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param File $file
     */
    public function removeCacheImage(File $file)
    {
//        // get container
//        $container = $this->container;
//
//        // get li ip bundle configs
//        $filterConfigurations = $container->get( 'liip_imagine.filter.configuration' );
//
//        // todo generate cache image path from bundle
//        // get root dir
//        $kernelDir = $container->get('kernel')->getRootDir();
//        $cachePath = $kernelDir . '/../web/media/cache';
//
//        // get filters
//        $filters = $filterConfigurations->all();
//
//        // get filter names
//        $filters = array_keys($filters);
//
//        // get path
//        $path = $file->getUploadDir(). '/' . $file->getPath();
//
//        // loop for fil
//        foreach ($filters as $filter){
//
//            if($filter == 'cache'){
//                continue;
//            }
//
//            // get path of cache image
//            $image = $cachePath . '/' . $filter . '/' . $path;
//
//            // unlink image
//            if(!is_dir($image) && file_exists($image)){
//                unlink($image);
//            }
//
//        }
        $container = $this->container;

        // get li ip bundle configs
        $filterConfigurations = $container->get( 'liip_imagine.filter.configuration' );
        // get filters
        $filters = $filterConfigurations->all();
        // get filter names
        $filters = array_keys($filters);
        // get cache manager
        $cacheManager = $container->get('liip_imagine.cache.manager');
        // clear file from cache
        $cacheManager->remove($file->getUploadDir(). '/' . $file->getPath(),$filters);

    }

    /**
     * @param $path
     */
    public function cachingImage($path)
    {
        // get container
        $container = $this->container;

        // get liip bundle configs
        $filterConfigurations = $container->get( 'liip_imagine.filter.configuration' );

        // get all configs
        $filterConfigurations = $filterConfigurations->all();

        // check configs
        if($filterConfigurations ){

            // get liip cache manager
            $cacheManager = $this->container->get('liip_imagine.cache.manager');

            // get liip filter manager
            $filterManager = $this->container->get('liip_imagine.filter.manager');

            // get data manager
            $dataManager = $container->get('liip_imagine.data.manager');

            // loop for configs
            foreach($filterConfigurations as $key => $filterConfiguration){

                // check has http in path
                if(strpos($path, 'http') === false){

                    // try to cache image
                    try{

                        // get binary of filter
                        $binary = $dataManager->find($key, $path);

                        // cache images
                        $cacheManager->store(
                            $filterManager->applyFilter($binary, $key),
                            $path,
                            $key);
                    }
                    catch(\Exception $e){
                        // catch
                    }
                }

            }
        }
    }


    /**
     * @param $toUser
     * @param $subject
     * @param $content
     */
    public function sendMessageFromAdmin($toUser, $subject, $content)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // find admin user
        $adminUser = $em->getRepository("LBUserBundle:User")->findOneBy(array('username' => 'admin'));

        // check admin user
        if($adminUser){

            if($adminUser->getId() != $toUser->getId()){

                $userRelation = $em->getRepository("LBUserBundle:User")->findUserRelation($adminUser->getId(), $toUser->getId());
                // create empty user relation with admin for new user
                if(!$userRelation){
                    $userRelation = new UserRelation();
                    $userRelation->setFromUser($adminUser);
                    $userRelation->setToUser($toUser);
                    $em->persist($userRelation);
                    $em->flush();
                }
            }

            // insert message
            $em->getRepository("LBMessageBundle:Message")->insertMessage($adminUser->getId(), $toUser->getId(),
                $subject, $content);
        }
    }


    /**
     * @param InterestGroup $group
     * @param string $type
     * @return string
     */
    public function generateAreasAndSki(InterestGroup $group, $type = 'web')
    {
        $areaAndSki = array();
        $key = 'none';

        // loop for interest
        foreach($group->getInterest() as $interest){

            // get name
            $name = $interest->getName();

            // check is upper case
            if($name == strtoupper($name)){
                $key = trim($name);
                $areaAndSki[$key]['image'] = $type == 'web' ? $interest->getDownloadLink() :
                    $interest->getDownloadLinkForMobile();
            }
            else{
                $areaAndSki[$key]['interests'][$interest->getId()] = trim($name);
            }
        }
        return $areaAndSki;

    }

    /**
     * @param $zipCode
     * @return ZipCode|object
     */
    public function getZipObjByZipCode($zipCode)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // get zip code
        $zipObject = $em->getRepository('LBUserBundle:ZipCode')->findOneBy(array('code' => $zipCode));

        // check zip code and create new
        if(!$zipObject){

            $geoData = $this->getLocationByZipCode($zipCode);

            if($geoData){

                $coordinates = $geoData->getCoordinates();
                $lat = $coordinates->getLatitude();
                $lng = $coordinates->getLongitude();

                if($lat && $lng){
                    $zipObject = new ZipCode();
                    $zipObject->setCode($zipCode);
                    $zipObject->setLat($lat);
                    $zipObject->setLng($lng);
                    $em->persist($zipObject);
                    $em->flush();
                }
            }
        }

        return $zipObject;
    }

    /**
     * This function create connection ti google maps
     * include bundle and search by zip code
     * return geo location data
     *
     * @param $zipCode
     * @return \Geocoder\Model\Address
     */
    public function getLocationByZipCode($zipCode)
    {

        try{
            // get CURL
            $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
            // get connection to googleMaps
            $geocoder = new \Geocoder\Provider\GoogleMaps($curl);
            // get first of Geo data
            $geoData =  $geocoder->geocode($zipCode)->first();

        }
        catch (\Exception $e){
            $geoData = null;
        }

        // return data
        return $geoData;
    }

    /**
     * @param $city
     * @return array|null
     */
    public function getLocationByCityName($city)
    {
        $city = urlencode($city);

        $googleApi = "http://maps.googleapis.com/maps/api/geocode/json";

        //generate geo coding url for get place data by lang and long
        $url = sprintf('%s?address=%s&language=en', $googleApi, $city);

        try{
            // get CURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //get response
            $response = curl_exec($ch);

            //close curl
            curl_close($ch);

            //json decode data
            $response = json_decode($response, true);

            // check response
            if(is_array($response) && $response['status'] == 'OK'){

                $result = $response['results']; // get result
                $result = reset($result); // get first data

                $address = $result['formatted_address']; // get address
                $address = explode(', ', $address);

                array_walk($address, function (&$item){
                    $item = explode(' ', $item);

                    if(count($item) > 1){
                        foreach ($item as $key =>$i){
                            if(is_numeric($i)){
                                unset($item[$key]);
                            }
                        }
                    }
                    $item = implode(' ', $item);
                });

                $address = implode(', ', $address);

                $location = $result['geometry']['location']; // get location

                // address
                $geoData = array(
                    'address' => $address,
                    'loc' => array(
                        'lat' => $location['lat'],
                        'lng' => $location['lng']
                    ));


            }else{
                $geoData = null;
            }

        }
        catch (\Exception $e){
            $geoData = null;
        }

        // return data
        return $geoData;
    }



    /**
     * @return string
     */
    public function generateUId()
    {
        do {
            $string = $this->randomString();
            $isUser = $this->container->get('doctrine')->getRepository('LBUserBundle:User')->findOneBy(array('uId' => $string));
        } while ($isUser);

        return $string;
    }

    /**
     * @param int $length
     * @return string
     */
    public function randomString($length = 8)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $actionName
     * @param User $user
     * @return bool
     */
    public function checkAction($actionName, User $user)
    {
        // result
        $result = true;

        // get user step
        $step = $user->getStep();

        if($step != 1 && $step != 2 ){
            return $result;
        }

        if(in_array($actionName, self::$STEP_2_ACTION) && $step == User::SECOND){
            return $result;
        }

//        if(in_array($actionName, self::$STEP_3_ACTION) && !in_array(User::THIRD, $step) ){
//            $result = 2;
//        }

        return $result = $step;
        
    }
}