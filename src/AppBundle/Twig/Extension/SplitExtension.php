<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/10/15
 * Time: 7:12 PM
 */

namespace AppBundle\Twig\Extension;


/**
 * Class SplitExtension
 * @package AppBundle\Twig\Extension
 */
class SplitExtension extends \Twig_Extension
{
    const SPLIT = '[...]';

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('blogSplit', array($this, 'blogSplit')),
        );
    }


    /**
     * @param $content
     * @param bool|true $split
     * @return string
     */
    public function blogSplit($content, $split = true)
    {
        if($split){
            $array = explode(self::SPLIT, $content);

            if(count($array) > 1){
                return $array[0] . ' . . .';
            }
        }
       else{
           return str_replace(self::SPLIT, '', $content);
       }

        return $content;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'blog_split';
    }
}