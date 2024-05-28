<?php

namespace LB\MessageBundle\Controller;

use AppBundle\Annotation\Paid;
use AppBundle\Traits\CheckAccess;
use LB\PaymentBundle\Entity\Subscriber;
use LB\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MainController extends Controller
{
    // check access trait
    use CheckAccess;

    /**
     * @Route("/messages/{uid}", defaults={"uid" = null}, name="message_users")
     * @Template()
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @param null $uid
     * @return array
     */
    public function indexAction(Request $request, $uid = null)
    {
        // check for "view" access
        if($error = $this->checkAccess($this->getUser(), 'message_users', $request)){
            return $error;
        };


        $id = null;

        if($uid){
            //get entity manager
            $em = $this->getDoctrine()->getManager();

            // get user by id
            $user = $em->getRepository('LBUserBundle:User')->findUserWithRelationsByUID($uid);

            if(!$user){
                throw $this->createNotFoundException('User not found');
            }
            $id = $user->getId();
        }

        return array('userId' => $id, 'uid' => $uid);

    }

    /**
     * This function is used for annotation reader
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function howMatchAction()
    {
        return $this->redirectToRoute('how-match');
    }
}