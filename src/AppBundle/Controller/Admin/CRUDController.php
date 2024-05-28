<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/23/15
 * Time: 12:24 PM
 */
namespace AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CRUDController extends Controller
{
    /**
     * @param $id
     * @return RedirectResponse
     */
    public function upAction($id)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($object->getPosition() == $this->admin->minPosition){
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $em = $this->getDoctrine()->getManager();
        $lastObject = $em->getRepository('AppBundle:InterestGroup')->findOneBy(array('position' => $object->getPosition() - 1));
        $lastObject->setPosition($object->getPosition());
        $object->setPosition($object->getPosition() - 1);
        $em->flush();

        $this->addFlash('sonata_flash_success', 'Success!');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function downAction($id)
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($object->getPosition() == $this->admin->maxPosition){
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $em = $this->getDoctrine()->getManager();
        $nextObject = $em->getRepository('AppBundle:InterestGroup')->findOneBy(array('position' => $object->getPosition() + 1));
        $nextObject->setPosition($object->getPosition());
        $object->setPosition($object->getPosition() + 1);
        $em->flush();

        $this->addFlash('sonata_flash_success', 'Success!');

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}