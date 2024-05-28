<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 1/13/16
 * Time: 3:50 PM
 */

namespace LB\UserBundle\DoctrineFilter;

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetaData;

/**
 * Class UserDeactivateFilter
 * @package LB\UserBundle\DoctrineFilter
 */
class UserDeactivateFilter extends SQLFilter
{
    /**
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->reflClass->name == 'LB\UserBundle\Entity\User') {
            return $targetTableAlias . ".deactivate != 1" . ' or ' . $targetTableAlias . '.deactivate is null ';
        }

        return "";
    }

}