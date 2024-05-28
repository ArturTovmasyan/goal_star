<?php

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Report;
use LB\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Rest\RouteResource("Report")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class ReportController extends FOSRestController
{
    /**
     * This function is used to create Report.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Report",
     *  description="This function is used to create new Report.",
     *  statusCodes={
     *         201="Returned when created",
     *         400="Bad Request",
     *         404="Return when user not found",
     *         401="Not authorized user",
     *     },
     * parameters={
     *      {"name"="message", "dataType"="string", "required"=true, "description"="Report message" },
     * }
     * )
     */
    public function putAction(Request $request, $userId)
    {
        //get from user
        $fromUser = $this->get('security.token_storage')->getToken()->getUser();

        //check if not logged in user
        if(!is_object($fromUser)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "There is not any user logged in");
        }

        //get entity manager
        $em = $this->getDoctrine()->getManager();

        //get translator
        $tr = $this->get('translator');

        // get all data
        $data = $request->request->all();

        if(!(int)$userId) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Invalid userId");
        }

        //get toUser
        $toUser = $em->getRepository('LBUserBundle:User')->find($userId);

        //check if to user not found
        if(!$toUser) {
            // return 404 if toUser not found
            throw new HttpException(Response::HTTP_NOT_FOUND, "User by id $userId not found");
        }

        //get request data
        $content = array_key_exists('message', $data) ? $data['message'] : null;

        if (!$content) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Content don't defined");
        }

        if($fromUser->getId() == $toUser->getId()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Users cannot send report for itself");
        }

        //get from email in parameter
        $fromEmail = $this->getParameter('to_report_email');

        //get from user name
        $fromUserName = $fromUser->getUserName();

        //get to user name
        $toUserName = $toUser->getUsername();

        //get to email in parameter
        $toEmail = $this->getParameter('to_report_email');

        // get mandrill service
        $mandrill = $this->container->get("app.mandrill");

        // get content
        $messageContent = $tr->trans('message', array('%fromUser%' => $fromUserName, '%toUser%' => $toUserName), 'email');

        // send email via mandrill
        $mandrill->sendEmail($toEmail, "Mike", 'luvbyrd', $messageContent);

//        // send message
//        $message = \Swift_Message::newInstance()
//            ->setSubject($tr->trans('subject', array(), 'email'))
//            ->setFrom($fromEmail)
//            ->setTo($toEmail)
//            ->setContentType('text/plain; charset=UTF-8')
//            ->setBody($tr->trans('message', array('%fromUser%' => $fromUserName, '%toUser%' => $toUserName), 'email'), 'text/plain');
//
//        $this->container->get('mailer')->send($message);

        //create new report
        $report = new Report();

        $report->setFromUser($fromUser);
        $report->setToUser($toUser);
        $report->setContent($content);

        $em->persist($report);
        $em->flush();

        return new Response('', Response::HTTP_CREATED);
    }

}