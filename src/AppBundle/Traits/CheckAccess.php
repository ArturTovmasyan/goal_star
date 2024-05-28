<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/24/16
 * Time: 1:40 PM
 */
namespace AppBundle\Traits;

use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use LB\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CheckAccess
 * @package AppBundle\Traits
 */
trait CheckAccess
{
    /**
     * @param $actionName
     * @param $user
     * @param Request $request
     * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkAccess($user, $actionName, Request $request)
    {
        if($this instanceof Controller){
            $check = $this->get('app.luvbyrd.service')->checkAction($actionName, $user);

            if ($check !== true) {

                $url = $check == 1 ? 'register_step_2' : 'register_step_3';
                $link = $this->generateUrl($url);

                $result = [
                    'step' => $check,
                    'link' => $link
                ];

                $referer = $this->generateUrl('members');
                $request->getSession()->getFlashBag()->add
                (
                    'accessNotice',
                   json_encode($result)
                );

                return $this->redirect($referer);
            }
        }

        return null;
    }

}