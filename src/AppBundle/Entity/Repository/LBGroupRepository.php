<?php
/**
 * Created by PhpStorm.
 * User: tigran
 * Date: 11/30/15
 * Time: 5:51 PM
 */

namespace AppBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LBGroupRepository extends EntityRepository
{
    /**
     * This repository find groups where current user invited
     *
     * @param $userId
     * @param $date
     * @return array
     */
    public function findInvitedByUser($userId, $date)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lg, author, lgmod, lgmem, modu, memu FROM AppBundle:LBGroup lg
                            LEFT JOIN lg.author author
                            LEFT JOIN lg.moderators lgmod
                            LEFT JOIN lgmod.moderator modu
                            LEFT JOIN lg.members lgmem
                            LEFT JOIN lgmem.member memu
                            WHERE (modu.id = :user_id
                                  AND lgmod.authorStatus = 1
                                  AND lgmod.moderatorStatus = 0)
                              OR (memu.id = :user_id
                                  AND lgmem.authorStatus = 1
                                  AND lgmem.memberStatus =0)
                              AND lg.eventDate >= :day
                            ORDER BY lg.eventDate ASC
                            ')
            ->setParameters(array('user_id'=>$userId, 'day'=>$date))
            ->getResult()
            ;
    }

    /**
     * @param $slug
     * @return array
     */
    public function getOneById($slug)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lg, author, lgmod, modu, lgmem, memu, ap, mup, mp
                            FROM AppBundle:LBGroup lg
                            LEFT JOIN lg.author author
                            LEFT JOIN author.profileImage ap
                            LEFT JOIN lg.moderators lgmod
                            LEFT JOIN lgmod.moderator modu
                            LEFT JOIN modu.profileImage mup
                            LEFT JOIN lg.members lgmem
                            LEFT JOIN lgmem.member memu
                            LEFT JOIN memu.profileImage mp
                            WHERE lg.slug =:slug
                            ')
            ->setParameter('slug', reset($slug))
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
            ;
    }

    /**
     * This repository find groups where current user Joined
     *
     * @param $userId
     * @param $date
     * @return array
     */
    public function findJoinedByUser($userId, $date)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lg, author, lgmod, lgmem, modu, memu FROM AppBundle:LBGroup lg
                            LEFT JOIN lg.author author
                            LEFT JOIN lg.moderators lgmod
                            LEFT JOIN lgmod.moderator modu
                            LEFT JOIN lg.members lgmem
                            LEFT JOIN lgmem.member memu
                            WHERE (modu.id = :user_id
                                  AND lgmod.authorStatus = 1
                                  AND lgmod.moderatorStatus = 1)
                              OR (memu.id = :user_id
                                  AND lgmem.authorStatus = 1
                                  AND lgmem.memberStatus = 1)
                              AND lg.eventDate >= :day
                            ORDER BY lg.eventDate ASC
                            ')
            ->setParameters(array('user_id'=>$userId, 'day'=>$date))
            ->getResult()
            ;
    }

    /**
     * This repository find groups where current user Author
     *
     * @param $userId
     * @param $date
     * @return array
     */
    public function findHostingByUser($userId, $date)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lg, author, lgmod, modu, lgmem, memu FROM AppBundle:LBGroup lg
                            LEFT JOIN lg.author author
                            LEFT JOIN lg.moderators lgmod
                            LEFT JOIN lgmod.moderator modu
                            LEFT JOIN lg.members lgmem
                            LEFT JOIN lgmem.member memu
                            WHERE author.id = :user_id AND lg.eventDate >= :day
                            ORDER BY lg.eventDate ASC
                            ')
            ->setParameters(array('user_id'=>$userId, 'day'=>$date))
            ->getResult()
            ;
    }

    /**
     * This repository find data for calendar by current user Id and path name
     * begin from current day and order by event day
     *
     * @param $userId
     * @param $type
     * @return array
     */
    public function findCalendarData($userId, $type)
    {
        $date = new \DateTime('now');
        $query = $this->getEntityManager()
            ->createQueryBuilder()
        ->select('DISTINCT(DATE (lg.eventDate)) as date')
        ->from('AppBundle:LBGroup', 'lg')
        ->leftJoin('lg.author', 'au')
        ->leftJoin('lg.moderators', 'mod')
        ->leftJoin('mod.moderator', 'modu')
        ->leftJoin('lg.members', 'mmb')
        ->leftJoin('mmb.member', 'mmbu')
//        ->andWhere('lg.eventDate >= :day')
        ;

        switch($type){
            case 'group_list':
                break;
            case 'group_invite_list':
                    $query->andWhere('(modu.id = :user_id and mod.authorStatus = 1 and mod.moderatorStatus = 0)
                                        or (mmbu.id = :user_id and mmb.authorStatus = 1 and mmb.memberStatus = 0)');
                    $query ->setParameter('user_id', $userId);
                break;
            case 'group_joined_list':
                    $query->andWhere('(modu.id = :user_id and mod.authorStatus = 1 and mod.moderatorStatus = 1)
                                        or (mmbu.id = :user_id and mmb.authorStatus = 1 and mmb.memberStatus = 1)');
                    $query ->setParameter('user_id', $userId);
                    break;
            case 'group_hosting_list':
                $query->andWhere('au.id = :user_id');
                $query ->setParameter('user_id', $userId);
                break;
            default:
                $query->andWhere('au.id = :user_id')
                    ->orWhere('modu.id = :user_id and mod.authorStatus = 1 and mod.moderatorStatus =1')
                    ->orWhere('mmbu.id = :user_id and mmb.authorStatus = 1 and mmb.memberStatus = 1');
                $query ->setParameter('user_id', $userId);
                break;
        }

        $query
//            ->setParameter('day', $date)
        ->orderBy('lg.eventDate', 'ASC')
       ;

        return $query->getQuery()->getArrayResult();
    }

    /**
     * This repository find groups by requested date
     *
     * @param $day
     * @return array
     */
    public function findByCalendarDataByDay($day)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('lg, au, mod, modu, mmb, mmbu')
            ->from('AppBundle:LBGroup', 'lg')
            ->leftJoin('lg.author', 'au')
            ->leftJoin('lg.moderators', 'mod')
            ->leftJoin('mod.moderator', 'modu')
            ->leftJoin('lg.members', 'mmb')
            ->leftJoin('mmb.member', 'mmbu')
        ;
        if($day != null)
        {
            $query
                ->where("lg.eventDate LIKE :minDay")
                ->setParameter('minDay', '%'.$day.'%')
            ;
        }

        return $query->getQuery()->getResult();
    }

    /**
     * This repository find data for calendar by current user Id and path name
     * begin from current day and order by event day
     *
     * @param $userId
     * @param $type
     * @param $minDay
     * @param $maxDay
     * @param null $start
     * @param null $count
     * @return array
     */
    public function findByCalendarData($userId, $type, $minDay, $maxDay,  $start = null, $count = null)
    {

        $query = $this->getEntityManager()
            ->createQueryBuilder()
        ->select('lg, au, mod, modu, mmb, mmbu')
        ->from('AppBundle:LBGroup', 'lg')
        ->leftJoin('lg.author', 'au')
        ->leftJoin('lg.moderators', 'mod')
        ->leftJoin('mod.moderator', 'modu')
        ->leftJoin('lg.members', 'mmb')
        ->leftJoin('mmb.member', 'mmbu')
        ;

        switch($type){
            case 'group_list':
                break;
            case 'group_invite_list':
                    $query->andWhere('(modu.id = :user_id and mod.authorStatus = 1 and mod.moderatorStatus = 0)
                                        or (mmbu.id = :user_id and mmb.authorStatus = 1 and mmb.memberStatus = 0)');
                    $query ->setParameter('user_id', $userId);
                break;
            case 'group_joined_list':
                    $query->andWhere('(modu.id = :user_id and mod.authorStatus = 1 and mod.moderatorStatus = 1)
                                        or (mmbu.id = :user_id and mmb.authorStatus = 1 and mmb.memberStatus = 1)');
                    $query ->setParameter('user_id', $userId);
                    break;
            case 'group_hosting_list':
                $query->andWhere('au.id = :user_id');
                $query ->setParameter('user_id', $userId);
                break;
            default:
                $query->andWhere('au.id = :user_id')
                    ->orWhere('modu.id = :user_id and mod.authorStatus = 1 and mod.moderatorStatus =1')
                    ->orWhere('mmbu.id = :user_id and mmb.authorStatus = 1 and mmb.memberStatus = 1');
                $query ->setParameter('user_id', $userId);
                break;
        }

        if($type == 'group_list'){

            if($maxDay){
                $query
                    ->andWhere('lg.eventDate >= :minDay AND lg.eventDate <= :maxDay')
                    ->setParameter('maxDay', $maxDay)
                    ->setParameter('minDay', $minDay);
            }
        }
        else{
            $query
                ->andWhere('lg.eventDate >= :minDay')
                ->setParameter('minDay', new \DateTime('now'))
            ;
        }
        $query->orderBy('lg.eventDate', 'ASC')
       ;

        // check is start and count, and return pagination
        if(($start || $count) && $type == "group_list"){

            // et max result if count exist
            if($count){
                $query
                    ->setMaxResults($count);
            }

            // set first result if start count exist
            if($start != null){
                $query
                    ->setFirstResult($start);
            }

            $paginator = new Paginator($query->getQuery(), $fetchJoinCollection = true);
            // get result
            return  array('count' => $paginator->count(), 'data' => $paginator->getIterator()->getArrayCopy());
        }
        else{
            return $query->getQuery()->getResult();
        }

    }

    /**
     * This repository find All groups
     *
     * @param $date
     * @return array
     */
    public function findAllForList($date)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT lg FROM AppBundle:LBGroup lg
                           LEFT JOIN lg.author au
                            LEFT JOIN lg.moderators mod
                            LEFT JOIN mod.moderator modu
                            LEFT JOIN lg.members mmb
                            LEFT JOIN mmb.member mmbu
                            WHERE lg.eventDate >= :day
                            ORDER BY lg.eventDate ASC
                            ')
            ->setParameter('day', $date)
            ->getResult();
    }

    /**
     * This function find old groups
     *
     * @return array
     */
    public function findOld()
    {
        $date = new \DateTime('now');
        return $this->getEntityManager()
            ->createQuery('SELECT lg.slug FROM AppBundle:LBGroup lg
                            WHERE lg.eventDate < :day
                            ORDER BY lg.id ASC
                            ')
            ->setParameter('day', $date)
            ->getResult();
    }

}