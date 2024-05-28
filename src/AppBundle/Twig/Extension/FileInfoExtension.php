<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/8/15
 * Time: 5:26 PM
 */

namespace AppBundle\Twig\Extension;

/**
 * Class FileInfoExtension
 * @package AppBundle\Twig\Extension
 */
class FileInfoExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('fileInfo', array($this, 'fileInfo')),
        );
    }

    /**
     * @param $path
     * @return array
     */
    public function fileInfo($path)
    {
        // new info var
        $info = array();


        // get file info
        $imageInfo = @getimagesize($path);
        if($imageInfo){
            $info['width'] = $imageInfo[0];
            $info['height'] = $imageInfo[1];

            $diff = $imageInfo[0] / $imageInfo[1];

            $info['square'] = $diff == 1 ? true : false;
            $info['portrait'] = $diff < 1 ? true : false;
            $info['landscape'] = $diff > 1 ? true : false;
        }
        return $info;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_file_info';
    }
}