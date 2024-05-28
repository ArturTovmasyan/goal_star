<?php

namespace AppBundle\Controller\Rest;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Thread;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\Mapping as ORM;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @Rest\RouteResource("Comment")
 * @Rest\Prefix("/api/v1.0")
 * @Rest\NamePrefix("rest_")
 */
class CommentController extends FOSRestController
{
    /**
     * This function create comment.
     *
     * @ApiDoc(
     *  resource=true,
     *  section="Comment",
     *  description="This function get users for select2",
     *  statusCodes={
     *         200="Returned when created",
     *         400="Return when content not correct",
     *     },
     *  parameters={
     *      {"name"="type", "dataType"="string", "required"=true, "description"="Item name (group or blog)"},
     *      {"name"="slug", "dataType"="string", "required"=true, "description"="Item slug"},
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="Item Id"},
     *      {"name"="commentBodey", "dataType"="text", "required"=true, "description"="comment body"},
     * }
     * )
     * @return Comment
     * @Rest\View(serializerGroups={"lb_group_single_mobile"})
     * @Security("has_role('ROLE_USER')")
     */
    public function postAction(Request $request)
    {

        $validator = $this->container->get('validator');
        $type = $request->get('type');
        $slug = $request->get('slug');
        $id = $request->get('id');
        $commentBodey = $request->get('commentBodey');

        if($type != null && $slug != null && $id != null && $commentBodey != null){

            $em = $this->getDoctrine()->getManager();

            if($type == 'group')
            {
                $url = $this->container->get('router')->generate('group_view', array('slug' => $slug), true);
            }
            elseif($type == 'blog')
            {
                $url = $this->container->get('router')->generate('page', array('slug' => $slug), true);
            }

            $thread = $this->container->get('fos_comment.manager.thread')->findThreadById($id);

            if($thread == null)
            {
                $threads = $this->container->get('fos_comment.manager.thread')->findAllThreads();
                $thread = new Thread();
                $thread->setPermalink($url);
                $thread->setLastCommentAt(new \DateTime('now'));
                $thread->setId(max($threads)->getId() +1);

                $errors = $validator->validate($thread);

                    if(count($errors)>0)
                    {
                        $errorsString = (string)$errors;
                        return new JsonResponse("Comment can't created {$errorsString}", Response::HTTP_BAD_REQUEST);
                    }
                    else {
                        $em->persist($thread);
                        $em->flush();
                    }
                }

            $comment= new Comment();
            $comment->setAuthor($this->getUser());
            $comment->setBody($commentBodey);
            $comment->setThread($thread);
            $comment->setCreatedAt(new \DateTime('now'));
            $comment->setState(0);

            $errors = $validator->validate($comment);

            if(count($errors)>0)
            {
                $errorsString = (string)$errors;

                return new JsonResponse("Comment can't created {$errorsString}", Response::HTTP_BAD_REQUEST);
            }
            else
            {
                $em->persist($comment);
                $em->flush();

                return $comment;
            }

        }
        else
        {
            return new JsonResponse("Comment can't created", Response::HTTP_BAD_REQUEST);
        }
    }
}