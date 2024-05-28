<?php
/**
 * Created by PhpStorm.
 * User: andranik
 * Date: 11/03/15
 * Time: 14:10 PM
 */
namespace LB\MessageBundle\Model;

use Doctrine\ORM\Mapping;

/**
 * This interface is used for doctrine ResolveTargetEntityListener
 *
 * Interface MessageUserInterface
 * @package LB\MessageBundle\Model
 */
interface MessageUserInterface
{
    public function getId();

    public function getUsername();
}