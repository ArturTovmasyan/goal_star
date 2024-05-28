<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 11/27/15
 * Time: 12:07 PM
 */

namespace AppBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;

class LBGroupMembersRepository extends EntityRepository
{
    public function findUniqueData($userId, $groupId)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lm FROM AppBundle:LBGroupMembers lm
                            LEFT JOIN lm.member m
                            LEFT JOIN lm.lbGroup g
                            WHERE m.id = :user_id AND g.id = :group_id
                          ')
            ->setParameters(array('user_id'=>$userId, 'group_id'=>$groupId))
            ->getOneOrNullResult();
    }

    /**
     * @param $userId
     */
    public function removeGroupMemberByUser($userId)
    {
        $this->getEntityManager()
            ->createQuery("DELETE from AppBundle:LBGroupMembers lm
                           WHERE lm.member =:userId
                        ")
            ->setParameter('userId', $userId)
            ->execute();
    }

}