<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 12/16/15
 * Time: 11:06 AM
 */

namespace AppBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
    public function findCount($url)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT (DISTINCT cm.id) FROM AppBundle:Comment cm
                            LEFT JOIN cm.thread tr
                            WHERE tr.permalink = :url')
            ->setParameter('url', $url)
            ->getOneOrNullResult();
    }

    /**
     * @param $userId
     */
    public function removeCommentByUser($userId)
    {
        $this->getEntityManager()
            ->createQuery("DELETE from AppBundle:Comment cm
                           WHERE cm.author =:userId
                        ")
            ->setParameter('userId', $userId)
            ->execute();
    }

}