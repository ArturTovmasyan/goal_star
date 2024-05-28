<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/28/15
 * Time: 12:24 PM
 */
namespace AppBundle\Model;

class SearchData
{
   const SEARCH_MODEL = "search_model";
    /**
     * @var
     */
    public $ageFrom;

    /**
     * @var
     */
    public $ageTo;

    /**
     * @var
     */
    public $interests;

    /**
     * @var
     */
    public $skiAndRide;

    /**
     * @var
     */
    public $city;

    /**
     * @var
     */
    public $lookingFor;

    /**
     * @var
     */
    public $distance;

    /**
     * @var
     */
    public $zipCode;

    /**
     * @var
     */
    public $zipCrd;
}