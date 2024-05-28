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



class OneAllService
{
    const ONE_ALL_API = "https://luvbyrd.api.oneall.com";

    /**
     * @var
     */
    private $publicKey;

    /**
     * @var
     */
    private $privateKey;

    /**
     * @var
     */
    private $subDomain;

    /**
     * OneAllService constructor.
     * @param $publicKey
     * @param $privateKey
     * @param $subDomain
     */
    public function __construct($publicKey, $privateKey, $subDomain)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->subDomain = $subDomain;
    }

    public function shareContent()
    {
        //Your Site Settings
//        $site_subdomain = 'REPLACE WITH YOUR SITE SUBDOMAIN';
//        $site_public_key = 'REPLACE WITH YOUR SITE PUBLIC KEY';
//        $site_private_key = 'REPLACE WITH YOUR SITE PRIVATE KEY';

        //API Access Domain
        $siteDomain = $this->subDomain.'.api.oneall.com';

        //Connection Resource
//        $resource_uri = 'https://'.$siteDomain.'/connections.json';
//        $resource_uri = 'https://'.$siteDomain.'/sharing/messages.json';
        $resource_uri = 'https://'.$siteDomain.'/pages.json';

        //Setup connection
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $resource_uri);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_USERPWD, $this->publicKey . ":" . $this->privateKey);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, 0);

        //Send request
        $result_json = curl_exec($curl);
        curl_close($curl);

        //Done
        print_r($result_json);

    }

}