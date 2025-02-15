<?php

namespace AppBundle\Entity\Repository;
use LB\UserBundle\Entity\UserRelation;

/**
 * EventRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $date
     * @param $start
     * @param $limit
     * @param null $id
     * @return array
     */
    public function getFreshEvents($date, $start, $limit, $id = null)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ev')
            ->from('AppBundle:Event', 'ev')
            ->where('ev.end > :date')
            ->andWhere('ev.status = true')
            ->andWhere(':id is null or ev.id != :id')
            ->orderBy('ev.start')
            ->setParameter('date', $date)
            ->setParameter('id', $id)
        ;
        
        return $query->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     * @param $count
     * @param $userId
     * @return array
     */
    public function getEventUsers($id, $count, $userId)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('LBUserBundle:User', 'u')
            ->join('u.events', 'ev', 'WITH', 'ev.id = :id AND ev.status = true')
            ->setParameter('id', $id)
        ;

        if($userId){
            $query
                ->addSelect('(CASE WHEN ur.id IS NOT NULL THEN 1 ELSE 0 END) as HIDDEN fr')
                ->leftJoin('LBUserBundle:UserRelation', 'ur', 'WITH',
                              '(ur.fromUser = :user AND ur.toUser = u AND ur.fromStatus = :status AND ur.toStatus = :status) 
                            OR (ur.fromUser = u AND ur.toUser = :user AND ur.fromStatus = :status AND ur.toStatus = :status)')
            ->setParameter('user', $userId)
            ->setParameter('status', UserRelation::LIKE)
            ->orderBy('fr', 'DESC');
        }

        return $query->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     * @param $start
     * @param $limit
     * @return array
     */
    public function getEventUsersByLimit($id, $start, $limit)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('LBUserBundle:User', 'u')
            ->join('u.events', 'ev', 'WITH', 'ev.id = :id AND ev.status = true')
            ->setParameter('id', $id)
            ->setFirstResult($start)
            ->setMaxResults($limit)
        ;

        return $query->getQuery()
            ->getResult();
    }

}
