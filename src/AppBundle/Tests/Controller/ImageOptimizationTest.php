<?php

namespace AppBundle\Tests\Controller;

use Liip\ImagineBundle\Model\Binary;

class ImageOptimizationControllerTest extends BaseClass
{
    /**
     * This function is used to check image optimization by nodejs
     */
    public function testImageOptimizationForJpg()
    {
        //get image optimization processor service
        $processor = $this->container->get('app.post_processor.opt_post_processor');

        //get input value
        $input = __DIR__ . '/images/test.jpg';

        //get image file before optimization
        $currentFileSize = filesize($input);

        //get output value
        $output = __DIR__ . '/images/new.jpg';

        //get file content
        $content = file_get_contents($input);

        //create binary
        $binary = new Binary($content, 'image/jpg', 'jpg');

        //run optimization service
        $optimizeFile = $processor->process($binary);

        //put file content
        file_put_contents($output, $optimizeFile->getContent());

        //get file size after optimization
        $optimizeImageSize = filesize($output);

        //set normal optimization by percent
        $normalOptimization = 100 - ($optimizeImageSize * 100 / $currentFileSize);

        //set default status
        $status = false;

        //check if optimization percent is more then 50%
        if($normalOptimization > 42) {
            //set status
            $status = true;
        }

        unlink($output);

        // Assert that the response status code is 2xx
        $this->assertTrue($status, "Images optimization for PNG don't work correctly!");
    }

    /**
     * This function is used to check image optimization by nodejs
     */
    public function testImageOptimizationForPng()
    {
        //get image optimization processor service
        $processor = $this->container->get('app.post_processor.opt_post_processor');

        //get input value
        $input = __DIR__ . '/images/test1.png';

        //get image file before optimization
        $currentFileSize = filesize($input);

        //get output value
        $output = __DIR__ . '/images/new.png';

        //get file content
        $content = file_get_contents($input);

        //create binary
        $binary = new Binary($content, 'image/png', 'png');

        //run optimization service
        $optimizeFile = $processor->process($binary);

        //put file content
        file_put_contents($output, $optimizeFile->getContent());

        //get file size after optimization
        $optimizeImageSize = filesize($output);

        //set normal optimization by percent
        $normalOptimization = 100 - ($optimizeImageSize * 100 / $currentFileSize);

        //set default status
        $status = false;

        //check if optimization percent is more then 50%
        if($normalOptimization > 50) {
            //set status
            $status = true;
        }

        unlink($output);

        // Assert that the response status code is 2xx
        $this->assertTrue($status, "Images optimization for PNG don't work correctly!");
    }
}
