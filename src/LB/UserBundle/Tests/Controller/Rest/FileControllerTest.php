<?php
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 11/18/15
 * Time: 11:53 AM
 */
namespace AppBundle\Tests\Controller\Rest;

use LB\UserBundle\Tests\Controller\BaseClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileControllerTest extends BaseClass
{
    /**
     * This function is used to check PostUploadFile function in rest
     *
     */
    public function testPostUploadFile()
    {
        $oldPhotoPath = __DIR__ . '/old_photo.jpg';
        $photoPath = __DIR__ . '/photo.jpg';

        // copy photo path
        copy($oldPhotoPath, $photoPath);

        // new uploaded file
        $photo = new UploadedFile(
            $photoPath,
            'photo.jpg',
            'image/jpeg',
            123
        );

        $url = '/api/v1.0/files/uploads/files';

        // try to upload files
        $this->clientFrom->request(
            'POST',
            $url,
            array(),
            array('gallery_file' => $photo)
        );

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not upload files in postUploadFileAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check PostProfileImage function in rest
     *
     * @depends testPostUploadFile
     */
    public function testPostProfileImage()
    {
        // get file
        $file = $this->em->getRepository('LBUserBundle:File')->findOneBySize(123);

        $url = sprintf('/api/v1.0/files/%s/profiles/images', $file->getId());

        // try to set profile images by id
        $this->clientFrom->request('POST', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not set profile images by id in postProfileImageAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }
    }

    /**
     * This function is used to check GetAllFile function in rest
     * @depends testPostUploadFile
     */
    public function testGetAllFile()
    {
        $url = '/api/v1.0/file/all/file';

        // try to get all files
        $this->clientFrom->request('GET', $url);

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not get all files in getAllFileAction rest!");

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        //get content in response
        $responseResults = json_decode($this->clientFrom->getResponse()->getContent(), true);

        //get array in content
        $responseResult = $responseResults[0];

        //check json structure
        $this->assertArrayHasKey('web_path_for_mobile', $responseResult, 'Invalid web_path_for_mobile key in testGetAllFile rest json structure');
        $this->assertArrayHasKey('id', $responseResult, 'Invalid id key in testGetAllFile rest json structure');
    }

    /**
     * This function is used to check DeleteUploadFile function in rest
     *
     * @depends testPostProfileImage
     */
    public function testDeleteUploadFile()
    {
        // get file
        $file = $this->em->getRepository('LBUserBundle:File')->findOneBy(array('clientName' => 'test_image2.jpg'));

        $url = sprintf('/api/v1.0/files/%s/upload/file', $file->getId());

        // try to remove upload file by id
        $this->clientFrom->request('DELETE', $url);

        // Check that the profiler is enabled
        if ($profile = $this->clientFrom->getProfile()){
            // check the number of requests
            $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount(), "number of requests are much more greater than needed on page!");
        }

        // Assert that the response status code is 2xx
        $this->assertTrue($this->clientFrom->getResponse()->isSuccessful(), "can not remove upload file by id in deleteUploadFileAction rest!");
    }
}