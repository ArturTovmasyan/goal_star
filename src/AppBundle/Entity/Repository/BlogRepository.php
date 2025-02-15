<?php

namespace AppBundle\Entity\Repository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * BlogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BlogRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * This function is used to get all blog by tag slug
     *
     * @param $slug
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    /**
     * @param $slug
     * @param $author
     * @return array
     */
    public function findAllBlogs($slug, $author)
    {

        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('b, t, c, a')
            ->from('AppBundle:Blog', 'b')
            ->leftJoin('b.tags', 't')
            ->leftJoin('b.category', 'c')
            ->leftJoin('b.author', 'a')
        ;

        // check slug
        if($slug){

            $query
                ->andWhere('t.slug = :slug OR c.slug = :slug')
                ->setParameter('slug', $slug)
            ;
        }

        // check slug
        if($author){


            // for no authors
            if($author == -1){
                $query
                    ->andWhere('a.id IS NULL')
                ;
            }
            // for authors
            else{
                $query
                    ->andWhere('a.username = :username')
                    ->setParameter('username', $author)
                ;
            }

        }
        $query->groupBy('b.id');
        $query->addGroupBy('b, t, c, a')
        ->orderBy('b.created', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * @param $blog
     * @return array
     */
    public function findRelated($blog)
    {
        $tags = $blog->getTags();

        if($tags){
            $query = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('b')
                ->from('AppBundle:Blog', 'b')
                ->join('b.tags', 'bt')
                ->join('AppBundle:Tag', 't', 'with', 't in (:tags) and t.slug = bt.slug')
                ->andWhere('b.id != :id')
                ->setParameter('tags', $tags)
                ->setParameter('id', $blog->getId())
                ->setMaxResults(3)

            ;
            return $query->getQuery()->getResult();
        }

        return array();
    }
}
