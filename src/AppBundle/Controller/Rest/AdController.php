<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 11:33 AM
 */

namespace AppBundle\Controller\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @Rest\RouteResource("Ad")
 * @Rest\Prefix("/api/v1.0")
 */
class AdController extends FOSRestController
{
    /**
     *
     * This function is used to get ad by location
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Ad",
     *  description="This function is used to get ad by location",
     *  statusCodes={
     *         200="Returned when all ok",
     *         204="There is no information to send back"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"adGeo"})
     */
    public function cgetAction()
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get repository
        $em = $this->getDoctrine()->getManager();

        // get ad
        $ad = $em->getRepository('AppBundle:AdGeo')->findAllWithLocation();

        return $ad;
    }

    /**
     *
     * This function is used to get ad by location
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Ad",
     *  description="This function is used to get ad by location",
     *  statusCodes={
     *         200="Returned when all ok",
     *         404="There is no data"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"adGeo"})
     */
    public function getGeoAction($id)
    {
        //get current user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($currentUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        // get repository
        $em = $this->getDoctrine()->getManager();

        // get ad
        $ad = $em->getRepository('AppBundle:AdGeo')->find($id);

        if(!$ad){
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        return $ad;
    }

    /**
     * @Rest\Get("/api/v1.0/ads/{domain}/{zone}", name="rest_get_ad_add_by_domain_and_zone", options={"method_prefix"=false}, defaults={"_format"="json"})
     * This function is used to get ad by location
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Ad",
     *  description="This function is used to get ad by domain and zone",
     *  statusCodes={
     *         200="Returned when all ok",
     *         404="There is no data"
     *     }
     * )
     *
     * @Rest\View()
     */
    public function getAddByDomainAndZoneAction($domain, $zone)
    {
        $zone = str_replace(' ', '_', strtolower($zone));
        // get data
        $ad = $this->container->get('lsoft.ads.check_data')->checkData($domain, $zone);

//        // set data for profiler
//        $this->container->get('data_collector.ad_collector')->addData($domain, $zone, $ad);

        return $ad;
    }
}