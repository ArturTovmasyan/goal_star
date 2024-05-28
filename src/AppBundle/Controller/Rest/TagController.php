<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 1/11/16
 * Time: 12:14 PM
 */

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Report;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("Tag")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class TagController extends FOSRestController
{
    /**
     *
     * This function is used to get tag.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Tag",
     *  description="This function is used to get tag fro autocomplite",
     *  statusCodes={
     *         200="Returned when all ok",
     *     }
     * )
     *
     * @Rest\View()
     *
     */
    public function getAction()
    {
        // get search items
        $search = $this->get('request')->get('term');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get all tags
        $tags = $em->getRepository('AppBundle:Tag')->findAllForRest($search);

        // remove keys
        $result = array_map(function($value) { return $value['name']; }, $tags);

        return new Response(json_encode($result));
    }

}
