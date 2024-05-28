<?php

namespace LB\NotificationBundle\Controller;

use AppBundle\Model\EmailSettingsData;
use Doctrine\Common\Collections\Criteria;
use JMS\SecurityExtraBundle\Annotation\Secure;
use LB\NotificationBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/notes", name="notification")
     * @Template()
     * @Secure(roles="ROLE_USER")
     */
    public function notesAction($routeName = null)
    {
        //get entity manager
        $em = $this->getDoctrine()->getManager();

        // get current user
        $currentUser = $this->getUser();

        $blockUsers = $em->getRepository('LBUserBundle:User')->findUserBlocks($currentUser);

        // get user relations
        $userRelations = $em->getRepository("LBUserBundle:UserRelation")->findAllUnread($currentUser, $blockUsers);

        return array('routeName' => $routeName, 'userRelations' => $userRelations);
    }
}
