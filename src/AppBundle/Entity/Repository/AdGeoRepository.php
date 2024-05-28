<?php

namespace AppBundle\Entity\Repository;


class AdGeoRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function findAllWithLocation()
    {
        return $this->getEntityManager()
            ->createQuery("SELECT a from AppBundle:AdGeo a")
            ->getResult();
    }
}
