<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 11/27/15
 * Time: 12:07 PM
 */

namespace AppBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;

class LBGroupModeratorsRepository extends EntityRepository
{
    /**
     * this repository find data by user and groups ids
     *
     * @param $userId
     * @param $groupId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUniqueData($userId, $groupId)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lm FROM AppBundle:LBGroupModerators lm
                            LEFT JOIN lm.moderator m
                            LEFT JOIN lm.lbGroup g
                            WHERE m.id = :user_id AND g.id = :group_id
                          ')
            ->setParameters(array('user_id'=>$userId, 'group_id'=>$groupId))
            ->getOneOrNullResult();
    }

    /**
     * @param $userId
     */
    public function removeGroupModeratorByUser($userId)
    {
        $this->getEntityManager()
            ->createQuery("DELETE from AppBundle:LBGroupModerators lm
                           WHERE lm.moderator =:userId
                        ")
            ->setParameter('userId', $userId)
            ->execute();
    }

//    public function find

}