<?php

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
 * @Rest\RouteResource("Interest")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class InterestController extends FOSRestController
{
    /**
     *
     * This function is used to get interests ordered by positions.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Profile",
     *  description="This function is used to get interests ordered by positions.",
     *  statusCodes={
     *         200="Returned when all ok",
     *         401="Unauthorized user"
     *     }
     * )
     *
     * @Rest\View(serializerGroups={"interestGroup", "interestGroup_interest", "interest"})
     *
     */
    public function getAction()
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        $interestGroups = $em->getRepository('AppBundle:InterestGroup')->findAllOrderByPosition();

        return $interestGroups;
    }

}