<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/19/15
 * Time: 7:19 PM
 */

namespace LB\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


class FileRepository extends EntityRepository
{

    /**
     * @param $userId
     * @return array
     */
    public function findFileNamesByUser($userId)
    {
        $query =  $this->getEntityManager()
            ->createQuery(" SELECT f.clientName as name
                            FROM LBUserBundle:File f
                            JOIN f.user u
                            WHERE u.id = :userId
                            ")
            ->setParameter('userId', $userId)
            ->getResult()
        ;

        $result = array_map(function ($item){return $item['name'];}, $query);

        return $result;
    }

    /**
     * @param $ids
     * @return array
     */
    public function findByIDs($ids)
    {
        $query =  $this->getEntityManager()
            ->createQuery(" SELECT f
                            FROM LBUserBundle:File f
                            WHERE f.id in (:ids)
                            ")
            ->setParameter('ids', $ids)
            ->getResult()
        ;

        return $query;
    }


    /**
     * @param $filename
     * @param $user
     * @return array
     */
    public function findOneByFileNameAndUser($filename, $user)
    {
        $query =  $this->getEntityManager()
            ->createQuery(" SELECT f
                            FROM LBUserBundle:File f
                            JOIN f.user as u
                            WHERE u.id = :userId and f.name = :filename
                            ")
            ->setParameter('userId', $user->getId())
            ->setParameter('filename', $filename)
            ->getOneOrNullResult()
        ;

        return $query;
    }


    /**
     * @return array
     */
    public function findAllOlder()
    {
        // create new dae
        $date = new \DateTime('now');
        $query =  $this->getEntityManager()
            ->createQuery(" SELECT f, u
                            FROM LBUserBundle:File f
                            LEFT JOIN f.user u
                            WHERE u.id is null and TIMESTAMPDIFF( HOUR ,  f.updated,  :date ) > 2
                            ")
            ->setParameter('date', $date)
            ->getResult()
        ;

        return $query;
    }
}
