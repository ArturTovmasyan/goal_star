<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 3:44 PM
 */
namespace AppBundle\Tests\Controller\Rest;

use AppBundle\Tests\Controller\BaseClass;

class CommentControllerTest extends BaseClass
{
    /**
     * This function is used to check put comment rest
     */
    public function testPost()
    {
        //get slug
        $slug = $this->em->getRepository('AppBundle:Tag')->findOneBy(array('name' => 'Tag1'))->getSlug();

        //get thread by permalink
        $threadId = $this->em->getRepository('AppBundle:Thread')->findOneBy(array('permalink' => 'http://luvbyrd.loc/group/view/tag1'))->getId();

        //set comment
        $commentBody = 'Test for comment rest';

        //set array data
        $data = array('type' => 'group', 'slug' => $slug, 'id' => $threadId, 'commentBodey' => $commentBody);

        $url = '/api/v1.0/comments';

        //try to cretae comment for group
        $this->clientTo->request('POST', $url, $data);

        $content = json_decode($this->clientTo->getResponse()->getContent(), true);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientTo->getResponse()->isSuccessful(), "can not put comment in testPut rest!");

        $this->assertTrue(
            $this->clientTo->getResponse()->headers->contains('Content-Type', 'application/json'),
            $this->clientTo->getResponse()->headers
        );

        $this->assertArrayHasKey('body', $content, 'Invalid body key in get app version rest json structure');
        $this->assertArrayHasKey('created_at', $content, 'Invalid created_at key in comment testPut rest json structure');

        if(array_key_exists('author', $content)) {

            //get author in array
            $author = $content['author'];

            $this->assertArrayHasKey('id', $author, 'Invalid id key in comment testPut rest json structure');
            $this->assertArrayHasKey('profile_image_path_for_mobile', $author, 'Invalid profile_image_path_for_mobile key in comment testPut rest json structure');
            $this->assertArrayHasKey('show_name', $author, 'Invalid show_name key in comment testPut rest json structure');
            $this->assertArrayHasKey('last_name', $author, 'Invalid last_name key in comment testPut rest json structure');
            $this->assertEmpty($author['last_name'], 'last_name value is not empty in testPut rest json structure');
        }

        // Check that the profiler is enabled
        if ($profile = $this->clientTo->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }
}