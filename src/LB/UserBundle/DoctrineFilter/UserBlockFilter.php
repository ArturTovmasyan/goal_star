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


class UserBlockFilter extends SQLFilter
{
    /**
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
    {
//        $userId = $this->getParameter('currentUserId');
//        $userId = trim($userId, '\'');
//        $userId = (int)$userId;
//
//        if ($userId > 0 && $targetEntity->reflClass->name == 'LB\UserBundle\Entity\User') {
//
//            return "NOT EXISTS(
//                SELECT ur.id FROM user_relation ur WHERE 
//                (ur.from_user_id = " .$userId." AND ur.to_user_id = " . $targetTableAlias ." .id AND ur.to_status = 6) OR 
//                (ur.to_user_id = " .$userId." AND ur.from_user_id = " . $targetTableAlias ." .id AND ur.from_status = 6)
//            )";
//        }

        return "";
    }

}