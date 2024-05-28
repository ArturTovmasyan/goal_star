<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/30/15
 * Time: 1:24 PM
 */

namespace LB\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * Class UserRelation
 * @package LB\UserBundle\Entity
 *
 * @ORM\Entity(repositoryClass="LB\UserBundle\Entity\Repository\UserRelationRepository")
 * @ORM\Table(name="user_relation", uniqueConstraints={@ORM\UniqueConstraint(name="from_to_user", columns={"from_user_id", "to_user_id"})},
 * indexes={
 *          @ORM\Index(name="from_to_like", columns={"to_user_id", "from_status", "is_like_read_from"}),
 *          @ORM\Index(name="to_from_like", columns={"from_user_id", "to_status", "is_like_read_to"}),
 *          @ORM\Index(name="to_from_favorite", columns={"from_user_id", "to_favorite_status"}),
 *          @ORM\Index(name="from_to_favorite", columns={"to_user_id", "from_favorite_status"}),
 *          @ORM\Index(name="to_from_visitor", columns={"from_user_id", "to_visitor_status"}),
 *          @ORM\Index(name="from_to_visitor", columns={"to_user_id", "from_visitor_status"}),
 *          @ORM\Index(name="statuses", columns={"to_status", "from_status"}),
 * })
 * )
 */
class UserRelation
{
    const LIKE         = 0;
    const FAVORITE     = 1;
    const NEW_VISITOR  = 2;
    const MESSAGE      = 3;
    const FRIEND       = 4;
    const DENIED       = 5;
    const BLOCK        = 6;
    const NATIVE       = 7;
    const VISITOR      = 8;
    const NEW_FAVORITE = 9;
    const SPAM = 10;
    const HIDE = 11;
    const LIKED_BY_ME = 12;
    const FAVORITE_BY_ME = 13;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="from_user_id", referencedColumnName="id")
     */
    protected $fromUser;

    /**
     * @ORM\Column(name="from_status", type="smallint", nullable=false)
     * @Groups("user_for_mobile")
     */
    protected $fromStatus = self::NATIVE;

    /**
     * @var
     * @ORM\Column(name="from_status_created", type="datetime", nullable=true)
     */
    protected $fromStatusCreated;


    /**
     * @ORM\Column(name="from_conversation", type="smallint", nullable=false)
     */
    protected $fromConversation = self::NATIVE;


    /**
     * @ORM\Column(name="to_conversation", type="smallint", nullable=false)
     */
    protected $toConversation = self::NATIVE;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="to_user_id", referencedColumnName="id")
     */
    protected $toUser;

    /**
     * @ORM\Column(name="to_status", type="smallint", nullable=false)
     * @Groups("user_for_mobile")
     */
    protected $toStatus = self::NATIVE;

    /**
     * @var
     * @ORM\Column(name="to_status_created", type="datetime", nullable=true)
     */
    protected $toStatusCreated;

    /**
     * @ORM\Column(name="to_visitor_status", type="smallint", nullable=false)
     */
    protected $toVisitorStatus = self::NATIVE;

    /**
     * @var
     * @ORM\Column(name="to_visit_created", type="datetime", nullable=true)
     */
    protected $toVisitCreated;

    /**
     * @ORM\Column(name="from_visitor_status", type="smallint", nullable=false)
     */
    protected $fromVisitorStatus = self::NATIVE;

    /**
     * @ORM\Column(name="to_favorite_status", type="smallint", nullable=false)
     * @Groups("user_for_mobile")
     */
    protected $toFavoriteStatus = self::NATIVE;

    /**
     * @var
     * @ORM\Column(name="to_favorite_created", type="datetime", nullable=true)
     */
    protected $toFavoriteCreated;

    /**
     * @var
     * @ORM\Column(name="from_visit_created", type="datetime", nullable=true)
     */
    protected $fromVisitCreated;

    /**
     * @ORM\Column(name="from_favorite_status", type="smallint", nullable=false)
     * @Groups("user_for_mobile")
     */
    protected $fromFavoriteStatus = self::NATIVE;

    /**
     * @var
     * @ORM\Column(name="from_favorite_created", type="datetime", nullable=true)
     */
    protected $fromFavoriteCreated;

    /**
     * @ORM\Column(name="is_like_read_from", type="boolean", nullable=true)
     * @Groups("user_by_status")
     */
    protected $isLikeReadFrom;

    /**
     * @ORM\Column(name="is_like_read_to", type="boolean", nullable=true)
     * @Groups("user_by_status")
     */
    protected $isLikeReadTo ;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     * @Groups("user_for_mobile")
     */
    protected $created;

    public function __construct()
    {
        $this->created = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set fromUser
     *
     * @param \LB\UserBundle\Entity\User $fromUser
     *
     * @return User
     */
    public function setFromUser(\LB\UserBundle\Entity\User $fromUser = null)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get fromUser
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set toUser
     *
     * @param \LB\UserBundle\Entity\User $toUser
     *
     * @return User
     */
    public function setToUser(\LB\UserBundle\Entity\User $toUser = null)
    {
        $this->toUser = $toUser;

        return $this;
    }

    /**
     * Get toUser
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getToUser()
    {
        return $this->toUser;
    }

    /**
     * Set fromStatus
     *
     * @param integer $fromStatus
     *
     * @return UserRelation
     */
    public function setFromStatus($fromStatus)
    {
        $this->fromStatus = $fromStatus;

        $this->fromStatusCreated = new \DateTime();

        return $this;
    }

    /**
     * Get fromStatus
     *
     * @return integer
     */
    public function getFromStatus()
    {
        return $this->fromStatus;
    }

    /**
     * Set toStatus
     *
     * @param integer $toStatus
     *
     * @return UserRelation
     */
    public function setToStatus($toStatus)
    {
        $this->toStatus = $toStatus;

        $this->toStatusCreated = new \DateTime();


        return $this;
    }

    /**
     * Get toStatus
     *
     * @return integer
     */
    public function getToStatus()
    {
        return $this->toStatus;
    }

    /**
     * Set toVisitorStatus
     *
     * @param integer $toVisitorStatus
     *
     * @return UserRelation
     */
    public function setToVisitorStatus($toVisitorStatus)
    {
        $this->toVisitorStatus = $toVisitorStatus;

        $this->toVisitCreated = new \DateTime();

        return $this;
    }

    /**
     * Get toVisitorStatus
     *
     * @return integer
     */
    public function getToVisitorStatus()
    {
        return $this->toVisitorStatus;
    }

    /**
     * Set fromVisitorStatus
     *
     * @param integer $fromVisitorStatus
     *
     * @return UserRelation
     */
    public function setFromVisitorStatus($fromVisitorStatus)
    {
        $this->fromVisitorStatus = $fromVisitorStatus;

        $this->fromVisitCreated = new \DateTime();

        return $this;
    }

    /**
     * Get fromVisitorStatus
     *
     * @return integer
     */
    public function getFromVisitorStatus()
    {
        return $this->fromVisitorStatus;
    }

    /**
     * Set toFavoriteStatus
     *
     * @param integer $toFavoriteStatus
     *
     * @return UserRelation
     */
    public function setToFavoriteStatus($toFavoriteStatus)
    {
        $this->toFavoriteStatus = $toFavoriteStatus;

        $this->toFavoriteCreated = new \DateTime();

        return $this;
    }

    /**
     * Get toFavoriteStatus
     *
     * @return integer
     */
    public function getToFavoriteStatus()
    {
        return $this->toFavoriteStatus;
    }

    /**
     * Set fromFavoriteStatus
     *
     * @param integer $fromFavoriteStatus
     *
     * @return UserRelation
     */
    public function setFromFavoriteStatus($fromFavoriteStatus)
    {
        $this->fromFavoriteStatus = $fromFavoriteStatus;

        $this->fromFavoriteCreated = new \DateTime();

        return $this;
    }

    /**
     * Get fromFavoriteStatus
     *
     * @return integer
     */
    public function getFromFavoriteStatus()
    {
        return $this->fromFavoriteStatus;
    }

    /**
     * Set isLikeReadTo
     *
     * @param boolean $isLikeReadTo
     *
     * @return UserRelation
     */
    public function setIsLikeReadTo($isLikeReadTo = true)
    {
        $this->isLikeReadTo = $isLikeReadTo;

        return $this;
    }

    /**
     * Get isLikeReadTo
     *
     * @return boolean
     */
    public function getIsLikeReadTo()
    {
        return $this->isLikeReadTo;
    }

    /**
     * Set isLikeReadFrom
     *
     * @param boolean $isLikeReadFrom
     *
     * @return UserRelation
     */
    public function setIsLikeReadFrom($isLikeReadFrom = true)
    {
        $this->isLikeReadFrom = $isLikeReadFrom;

        return $this;
    }

    /**
     * Get isLikeReadFrom
     *
     * @return boolean
     */
    public function getIsLikeReadFrom()
    {
        return $this->isLikeReadFrom;
    }

    /**
     * Set fromConversation
     *
     * @param integer $fromConversation
     *
     * @return UserRelation
     */
    public function setFromConversation($fromConversation)
    {
        $this->fromConversation = $fromConversation;

        return $this;
    }

    /**
     * Get fromConversation
     *
     * @return integer
     */
    public function getFromConversation()
    {
        return $this->fromConversation;
    }

    /**
     * Set toConversation
     *
     * @param integer $toConversation
     *
     * @return UserRelation
     */
    public function setToConversation($toConversation)
    {
        $this->toConversation = $toConversation;

        return $this;
    }

    /**
     * Get toConversation
     *
     * @return integer
     */
    public function getToConversation()
    {
        return $this->toConversation;
    }

    /**
     * @return mixed
     */
    public function getFromStatusCreated()
    {
        return $this->fromStatusCreated;
    }

    /**
     * @param mixed $fromStatusCreated
     */
    public function setFromStatusCreated($fromStatusCreated)
    {
        $this->fromStatusCreated = $fromStatusCreated;
    }

    /**
     * @return mixed
     */
    public function getToStatusCreated()
    {
        return $this->toStatusCreated;
    }

    /**
     * @param mixed $toStatusCreated
     */
    public function setToStatusCreated($toStatusCreated)
    {
        $this->toStatusCreated = $toStatusCreated;
    }

    /**
     * @return mixed
     */
    public function getToVisitCreated()
    {
        return $this->toVisitCreated;
    }

    /**
     * @param mixed $toVisitCreated
     */
    public function setToVisitCreated($toVisitCreated)
    {
        $this->toVisitCreated = $toVisitCreated;
    }

    /**
     * @return mixed
     */
    public function getToFavoriteCreated()
    {
        return $this->toFavoriteCreated;
    }

    /**
     * @param mixed $toFavoriteCreated
     */
    public function setToFavoriteCreated($toFavoriteCreated)
    {
        $this->toFavoriteCreated = $toFavoriteCreated;
    }

    /**
     * @return mixed
     */
    public function getFromVisitCreated()
    {
        return $this->fromVisitCreated;
    }

    /**
     * @param mixed $fromVisitCreated
     */
    public function setFromVisitCreated($fromVisitCreated)
    {
        $this->fromVisitCreated = $fromVisitCreated;
    }

    /**
     * @return mixed
     */
    public function getFromFavoriteCreated()
    {
        return $this->fromFavoriteCreated;
    }

    /**
     * @param mixed $fromFavoriteCreated
     */
    public function setFromFavoriteCreated($fromFavoriteCreated)
    {
        $this->fromFavoriteCreated = $fromFavoriteCreated;
    }


}
