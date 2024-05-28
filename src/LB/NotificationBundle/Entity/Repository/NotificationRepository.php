<?php

namespace LB\NotificationBundle\Entity\Repository;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class NotificationRepository
 * @package LB\NotificationBundle\Entity\Repository
 */
class NotificationRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * This function is used to find all notifications by given user id and count
     *
     * @param $userId
     * @param $count
     * @return array
     */
    public function findAllNoteByUser($userId, $count = 10)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('nt')
            ->from('LBNotificationBundle:Notification', 'nt')
            ->join('nt.toUser', 'u')
            ->where('u.id = :userId')
            ->orderBy('nt.created', 'DESC')
            ->setParameter('userId' , $userId);
        //check if count set
        if($count > 0) {
            $query
                ->setMaxResults($count);
        }
        return $query->getQuery()->getResult();
    }

    /**
     * This function is used to find all unread notifications by given userId
     *
     * @param $userId
     * @return array
     */
    public function findUnReadNoteCount($userId)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT COUNT(nt)
                       FROM LBNotificationBundle:Notification nt
                       JOIN nt.toUser tu
                       WHERE tu.id = :userId AND nt.isRead = :isRead
                       ORDER BY nt.created DESC")
            ->setParameter('userId', $userId)
            ->setParameter('isRead', false)
            ->getSingleScalarResult();
    }

    /**
     * This function is used to find note by userId and status
     *
     * @param $userId
     * @param $status
     * @return array
     */
    public function findNoteByIdAndStatus($userId, $status)
    {
        return $this->getEntityManager()
        ->createQuery("SELECT nt
                       FROM LBNotificationBundle:Notification nt
                       JOIN nt.toUser tu
                       WHERE tu.id = :userId AND nt.status = :status
                       ORDER BY nt.created DESC")
        ->setParameter('userId', $userId)
        ->setParameter('status', $status)
        ->getResult();
    }

    /**
     * This function is used to remove all notifications by given user id
     *
     * @param $userId
     * @param $noteId
     * @return mixed
     */
    public function removeNote($userId, $noteId)
    {
        // get all ids
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('nt.id')
            ->from('LBNotificationBundle:Notification','nt')
            ->join('nt.toUser', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
        ;

        if($noteId != -1){
            $query
                ->andWhere('nt.id = :noteId')
                ->setParameter('noteId', $noteId)
            ;
        }

        //get ids
        $ids = $query->getQuery()->getResult();

        if($ids) {
            //remove note
            $this->getEntityManager()
                ->createQueryBuilder()
                ->delete('LBNotificationBundle:Notification','nt')
                ->where('nt.id in (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()->execute();
            ;
        }
    }

    /**
     * This function is used to update note read status
     *
     * @param $noteId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateNoteReadStatus($noteId)
    {
        $this->getEntityManager()
            ->createQuery("UPDATE LBNotificationBundle:Notification nt
                           SET nt.isRead = TRUE
                           WHERE  nt.id = :noteId
                           ")
            ->setParameter('noteId', $noteId)
            ->getResult();
    }

    /**
     * This function is used to find all unread notifications by given userId
     *
     * @param $userId
     * @return array
     */
    public function findCountByStatus($userId)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT CASE WHEN nt.status = 0 then 1 ELSE 0 END AS invited,
                                  CASE WHEN nt.status = 1 then 1 ELSE 0 END AS confirm,
                                  CASE WHEN nt.status = 2 then 1 ELSE 0 END AS request_to_admin,
                                  CASE WHEN nt.status = 3 then 1 ELSE 0 END AS confirm_from_admin,
                                  CASE WHEN nt.status = 4 then 1 ELSE 0 END AS remove,
                                  nt.link as link
                       FROM LBNotificationBundle:Notification nt
                       JOIN nt.toUser tu
                       WHERE tu.id = :userId AND nt.isRead = :isRead
                       ORDER BY nt.created DESC")
            ->setParameter('userId', $userId)
            ->setParameter('isRead', false)
            ->getResult();
    }

    public function findByUserIdAndGroup($userId, $url)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT nt FROM LBNotificationBundle:Notification nt
                            LEFT JOIN nt.toUser tou
                            LEFT JOIN nt.fromUser fru
                            WHERE tou.id = :user_id AND nt.link = :url")
            ->setParameters(array('user_id'=>$userId, 'url'=>$url))
            ->getResult();

    }

    /**
     * This repository find notifications by url's and remove
     *
     * @param $urls
     */
    public function removeByUrl($urls)
    {
        $query = $this->getEntityManager()
            ->createQuery('DELETE FROM LBNotificationBundle:Notification nt
							WHERE nt.link IN (:urls)')
            ->setParameter('urls', $urls);
        $query->execute();
    }

    /**
     * @param $userId
     */
    public function removeNoteByUser($userId)
    {
        $this->getEntityManager()
            ->createQuery("DELETE from LBNotificationBundle:Notification nt
                           WHERE nt.fromUser =:userId OR nt.toUser =:userId
                        ")
            ->setParameter('userId', $userId)
            ->execute();
    }

}
