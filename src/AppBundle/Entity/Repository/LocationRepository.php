<?php

namespace AppBundle\Entity\Repository;


/**
 * Class LocationRepository
 * @package AppBundle\Entity\Repository
 */
class LocationRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param $lng
     * @param $lat
     * @return array
     */
    public function findByCoordinates($lng, $lat)
    {
        //3959 search in miles
        //6371 search in km

        return $this->getEntityManager()
            ->createQuery("SELECT l from AppBundle:Location l
                           JOIN l.adGeo a
                           WHERE (3959 * acos(cos(radians(:lat)) * cos(radians(l.lat)) * cos(radians(l.lng) - radians(:lng)) + sin(radians(:lat)) * sin(radians(l.lat)))) < a.radius
                         ")
            ->setParameter('lat', $lat)
            ->setParameter('lng', $lng)
            ->getResult();
    }
}
