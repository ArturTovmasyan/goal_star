<?php

namespace AppBundle\Entity\Repository;

/**
 * Class LSoftAdManagerRepository
 * @package AppBundle\Entity\Repository
 */
class LSoftAdManagerRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return array
     */
    public function findManagerData()
    {
        $em = $this->getEntityManager();

        $dql= 'SELECT m, ad, i
               FROM AppBundle:LSoftAdManager m
               JOIN m.ad ad
               LEFT JOIN m.interests i
               INDEX BY ad.id
              ';

        // create query by dql
        $query = $em->createQuery($dql);

        // get result
        $visions = $query->getResult();

        $results = [];

        // loop for result
        foreach ($visions as $vision){
            $ad = $vision->getAd();
            $results[$ad->getId()] = $vision;
        }

        return $results;

    }
}
