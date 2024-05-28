<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 12/10/15
 * Time: 7:45 PM
 */

namespace AppBundle\Services;


use Symfony\Component\DependencyInjection\Container;

class Mailchimp
{
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
     * This function create/update/delete subscriber from Mailchimp
     *
     * @param $data
     * @return mixed
     */
    public function syncMailchimp($data)
    {
        try{

            // get user location date by current user
            $address = $this->getLocationByZipCode($data['zip_code']);
            $latitude =  $address->getCoordinates()->getLatitude();
            $longitude = $address->getCoordinates()->getLongitude();
            $country_code = $address->getCountryCode();
        }
        catch(\Exception $e){
            $address = '';
            $latitude = 0;
            $longitude = 0;
            $country_code = '';

        }


        // get mailchimp api key and mailchimp list id from parameters
        $apiKey = $this->container->getParameter('mailchimp_api_key');
        $listId = $this->container->getParameter('mailchimp_list_id');

        //hashing user email for security
        $memberId = md5(strtolower($data['email']));

        // get API kay prefix
        $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);
        // create connection url
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

        // create content an json
        $json = json_encode([
            'email_address' => $data['email'],
            'status' => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
            'zip' => $data['zip_code'],
            'location' => array(
                'latitude'=> $latitude,
                'longitude'=>$longitude,
                'country_code'=>$country_code
            ),
            $data['zip_code'],
            'merge_fields' => [
                'FNAME' => $data['firstname'],
                'LNAME' => $data['lastname'],
                'MMERGE3' => $data['birthday']
            ]
        ]);
        // open connection
        $ch = curl_init($url);
        // login by apiKay and PUT date
        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        // check result
        $result = curl_exec($ch);
        // check result status code
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // close init
        curl_close($ch);

        // return Http status code
        return $httpCode;
    }

    /**
     * This function create connection ti google maps
     * include bundle and search by zip code
     * return geo location data
     *
     * @param $zipCode
     * @return \Geocoder\Model\Address
     */
    protected function getLocationByZipCode($zipCode)
    {
        // get CURL
        $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
        // get connection to googleMaps
        $geocoder = new \Geocoder\Provider\GoogleMaps($curl);
        // get first of Geo data
        $geoData =  $geocoder->geocode($zipCode)->first();
        // return data
        return $geoData;
    }

}