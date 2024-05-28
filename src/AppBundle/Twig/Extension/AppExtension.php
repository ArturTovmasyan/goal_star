<?php

namespace AppBundle\Twig\Extension;

use AppBundle\Entity\InterestGroup;

class AppExtension extends \Twig_Extension
{
    /**
     * @var
     */
    private $container;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }



    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('show_fullName', array($this, 'nameFilter')),
            new \Twig_SimpleFilter('objToId', array($this, 'objToId')),
            new \Twig_SimpleFilter('mustShowGroupCalendar', array($this, 'mustShowGroupCalendar')),
            new \Twig_SimpleFilter('ucwords', array($this, 'ucwords')),
            new \Twig_SimpleFilter('jsonDecode', array($this, 'jsonDecode')),
            new \Twig_SimpleFilter('remove_asset_version', array($this, 'removeAssetVersion'),  array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('auto_link_text', array($this, 'auto_link_text')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('mustShowGroupCalendar', array($this, 'mustShowGroupCalendar')),
            new \Twig_SimpleFunction('generateAreAndSki', array($this, 'generateAreAndSki')),
        );
    }

    /**
     * @param $words
     * @return string
     */
    public function ucwords($words)
    {
        return  ucwords($words);
    }

    /**
     * @param $json
     * @return mixed
     */
    public function jsonDecode($json)
    {
        return  json_decode($json, true);
    }

    public function nameFilter($user)
    {
        return $this->container->get('app.full_name')->fullNameFilter($user);
    }

    /**
     * @param InterestGroup $group
     * @return mixed
     */
    public function generateAreAndSki(InterestGroup $group)
    {
        // generate extension
        $areaAndSki = $this->container->get('app.luvbyrd.service')->generateAreasAndSki($group);

        return $areaAndSki;
    }


    /**
     * @param $objects
     * @return string
     */
    public function objToId($objects)
    {
        $result = array();

        // is array
        if(is_array($objects)){

            //loop for object
            foreach($objects as $object){
                if(method_exists($object, 'getId')){
                    $result[] = $object->getId();
                }
            }
        }
        return json_encode($result);
    }

    static public function auto_link_text($string)
    {

        $regexp = "/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i";
        preg_match_all($regexp, $string, $matches, \PREG_SET_ORDER);
        $anchorMarkup = "<a href=\"%s\" target=\"_blank\" >%s</a>";
        $matchesArray = [];

        foreach ($matches as $match) {
            if (count($match) && !in_array($match[0], $matchesArray)) {
                $replace = sprintf($anchorMarkup, $match[0], $match[0]);
                $string = str_replace($match[0], $replace, $string);
                $matchesArray[] = $match[0];
            }
        }

        return $string;
    }


    /**
     * @return bool
     */
    public function mustShowGroupCalendar()
    {
        try{

            // get request
            $request = $this->container->get('request');

            // get controller from request
            $controller = $request->get("_controller");

            // get route
            $route = $request->get('_route');

            // pattern to get controller name
            $pattern = "/Controller\\\\([a-zA-Z]*)Controller/";

            // get name
            preg_match($pattern, $controller, $matches);

            // check is set matches 1
            if(isset($matches[1])){

                // get controller name
                $name = $matches[1];

                // check is name group and is rout group list
                if($name == "Group" && $route=='group_list'){
                    return true;
                }
            }

            return false;
        }
        catch(\Exception $e){
            return false;
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function removeAssetVersion($url)
    {
        $pos = strpos($url, '?');

        if($pos){
            $url = substr($url, 0, $pos);
        }

        return $url;
    }

    public function getName()
    {
        return 'app_extension';
    }
}