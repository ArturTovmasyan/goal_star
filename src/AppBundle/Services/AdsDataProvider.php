<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/15/16
 * Time: 4:38 PM
 */

namespace AppBundle\Services;

use LSoft\AdBundle\Entity\Ad;
use LSoft\AdBundle\Entity\AdAnalyticsProvider;
use LSoft\AdBundle\Entity\AdsAnalytics;
use LSoft\AdBundle\Entity\AdsProvider;
use LSoft\AdBundle\Entity\Domain;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class AdsDataProvider
 * @package AppBundle\Services
 */
class AdsDataProvider extends \LSoft\AdBundle\Service\AdsDataProvider
{
    const APC_CASH_ID = 'luvbyrd_apc_cache_ad';

    /**
     * This function get data
     *
     * @param $domain
     * @param $zone
     * @return mixed
     */
    public function checkData($domain, $zone)
    {
        // get entity manager
        $em = $this->container->get('doctrine')->getManager();
        // get key
        $key = $this->cretePattern($domain);

        //get lifetime from caching
        $lifetime = $this->container->getParameter('l_soft_ad.lifetime');

        $data = null;

        if(isset($this->adsData[$zone])){
            $data = $this->getDataForDomain($zone);
        }
        else{

            // get data bu domain
            $ads = $em->getRepository("LSoftAdBundle:Ad")->findByAdsManager($domain, $key, $lifetime);

            if($ads != null && count($ads)>0)
            {
                // set data in array by zone and domain
                foreach($ads as $ad)
                {
                    if($ad->getZone() != null)
                    {

                        $this->adsData[$ad->getZone()][] = $ad->getAd();

                        if($ad->getZone() == $zone)
                        {
                            $data = $this->getDataForDomain($zone);
                        }
                    }
                }
            }
        }

        if ($data != null) {
            //
            $this->adSingle[] = array('ad_name' => $data->getName(), 'index'=>(int)$data->getDimensionIndex(), 'domain' => $domain, 'zone' => $zone);
            $session = $this->container->get('session');
            $session->set('adData', $this->adSingle);
        }

        return $data;
    }

    /**
     * @param $zone
     * @return mixed
     */
    private function getDataForDomain($zone)
    {
        // check is array exist
        if(array_key_exists($zone, $this->adsData)){
            $ads = $this->adsData[$zone];

            // get current user
            $user = $this->container->get('security.token_storage')->getToken()->getUser();


            // check if user not exist
            if(!$user){
                return  reset($ads);
            }

            $managerData = $this->getManagerData();

            foreach ($ads as $ad){

                if(array_key_exists($ad->getId(), $managerData)){

                    $checkingData = $managerData[$ad->getId()];

                    if($this->checkUserWithAd($user, $checkingData)){
                        return $ad;
                    }

                }else{
                    return reset($ads);
                }
            }
        }

        return null;
    }

    /**
     * @param $user
     * @param $checkingData
     * @return bool
     */
    private function checkUserWithAd($user, $checkingData)
    {

        if($checkingData->getGender() && $user->getIAm() != $checkingData->getGender()){
            return false;

        }

        if($checkingData->getCity() && $user->getCity() != $checkingData->getCity()){
            return false;
        }

        if($checkingData->getMaxAge() && $checkingData->getMaxAge() < $user->getAge()){
            return false;

        }

        if($checkingData->getMinAge() && $checkingData->getMinAge() > $user->getAge() ){
            return false;
        }

        if($checkingData->getInterests()->count() > 0 &&
            !$this->checkInterests($user->getInterestsIds(), $checkingData->getInterests())){
                return false;
        }
        return true;
    }

    /**
     * @param $userInterestIds
     * @param $interests
     * @return bool
     */
    private function checkInterests($userInterestIds, $interests)
    {
        $interests = $interests->toArray();
        $interestsIds = array_map(function($interest){return $interest->getId();}, $interests);
        $intersect = array_intersect($userInterestIds, $interestsIds);
        if(count($intersect ) > 0){
            return true;
        }
        return false;

    }

    /**
     * @return array|mixed
     */
    private function getManagerData()
    {
        //get entity manager
        $em = $this->container->get('doctrine')->getManager();

        // check is apc exist
        if(apc_exists(self::APC_CASH_ID)){
            $managerData = apc_fetch(self::APC_CASH_ID);
        }else{
            // get data bu domain
            $managerData = $em->getRepository("AppBundle:LSoftAdManager")->findManagerData();
            apc_store(self::APC_CASH_ID, $managerData);
        }

        return $managerData;
    }
}