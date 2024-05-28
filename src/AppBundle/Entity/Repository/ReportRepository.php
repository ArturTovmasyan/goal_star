<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 1/12/16
 * Time: 1:41 PM
 */

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ReportRepository extends EntityRepository
{

    /**
     * @param $userId
     */
    public function removeReportByUser($userId)
    {
        $this->getEntityManager()
            ->createQuery("DELETE from AppBundle:Report r WHERE r.fromUser =:userId OR r.toUser =:userId
                        ")
            ->setParameter('userId', $userId)
            ->execute();
    }
}