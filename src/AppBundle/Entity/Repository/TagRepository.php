<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 1/11/16
 * Time: 12:16 PM
 */

namespace AppBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Class TagRepository
 * @package AppBundle\Entity\Repository
 */
class TagRepository extends EntityRepository
{

    /**
     * @param $search
     * @return array
     */
    public function findAllForRest($search)
    {
        // create query builder
        $queryBuilder =  $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('t.name')
            ->from('AppBundle:Tag' , 't')
            ->orderBy('t.name', 'ASC');

        if($search){
            $queryBuilder
                ->where('t.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }


    /**
     * @return array
     */
    public function findAllTagsName()
    {
        // create query builder
        $queryBuilder =  $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('t')
            ->from('AppBundle:Tag' , 't', 't.name')
            ->orderBy('t.name', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }
}