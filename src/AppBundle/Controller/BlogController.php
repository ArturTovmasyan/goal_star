<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 12:56 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Blog;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class BlogController
 * @package AppBundle\Controller
 * @Route("/")
 */
class BlogController extends Controller
{
    /**
     * This action return blog related
     *
     * @Route("/related/{slug}", name="blog_related")
     * @param Blog $blog
     * @return array
     * @ParamConverter("blog", class="AppBundle:Blog")
     */
    public function getRelatedAction(Blog $blog)
    {
        // get related blogs
        $related = $this->getDoctrine()->getManager()->getRepository('AppBundle:Blog')->findRelated($blog);

        return $this->render('@App/Blog/related.html.twig', array('related' => $related));
    }

    /**
     * This action return blog related
     *
     * @param Blog $blog
     * @return array
     * @ParamConverter("blog", class="AppBundle:Blog")
     */
    public function getCommentsAction(Blog $blog)
    {
        $url = $this->container->get('router')->generate('page', array('slug' => $blog->getSlug()), true);
        $count = $this->getDoctrine()->getManager()->getRepository('AppBundle:Comment')->findCount($url);

        return new JsonResponse($count['1']);
    }

    /**
     * @Route("/blog/comment/{id}", requirements={"id"="\d+"}, name="blog_comment")
     * @return Response
     * @Template()
     * @param $id
     */
    public function blogCommentAction($id)
    {
        return array(
            'id'   => $id
        );
    }
    
    /**
     * @Route("/blog/", name="blog_list")
     * @Route("/author/{author}/", name="blog_list_author")
     * @Route("/category/{slug}/", name="blog_list_category")
     * @Route("/tag/{slug}/", name="blog_list_tag")
     * @param null $slug
     * @param null $author
     * @return array
     * @Template
     */
    public function indexAction($slug = null, $author = null)
    {
        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // find all blog by tag slug
        $blogs = $em->getRepository('AppBundle:Blog')->findAllBlogs($slug, $author);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $blogs,
            $this->get('request')->query->get('page', 1),
            5
        );

        return array('pagination' => $pagination);
    }
}