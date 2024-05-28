<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/23/15
 * Time: 12:40 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use LB\UserBundle\Entity\File;
use LB\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LBGroupRepository")
 * @ORM\Table(name="lb_group", indexes={
 *          @ORM\Index(name="event_date", columns={"event_date"}),
 *          @ORM\Index(name="group_slug", columns={"slug"}),
 * })
 * )
 * @Assert\Callback(methods={"validate"})
 * @ORM\HasLifecycleCallbacks()
 *
 * Class Group
 * @package AppBundle\Entity
 */
class LBGroup
{
    // this trait is add file(UploadFile object) to this entity
    use \AppBundle\Traits\File;

    const GROUP_PUBLIC = 0;
    const GROUP_PRIVATE = 1;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"lb_group_single_mobile"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_single", "group_single_mobile", "lb_group_single_mobile"})
     */
    protected $name;

    /**
     * @var string slug
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_single", "lb_group_single_mobile"})
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Groups({"lb_group_single", "lb_group_single_mobile"})
     */
    protected $description;

    /**
     * @var
     * @Groups({"lb_group"})
     */
    public $imageCachePath = null;

    /**
     * @var string
     *
     * @Groups({"lb_group"})
     */
    public $groupCount;

    /**
     * @ORM\ManyToOne(targetEntity="LB\UserBundle\Entity\User")
     * @ORM\JoinColumn(fieldName="author_id", referencedColumnName="id")
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $author;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\LBGroupModerators", mappedBy="lbGroup", cascade={"remove"})
     * @Groups({"lb_group", "lb_group_single"})
     */
    protected $moderators;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\LBGroupMembers", mappedBy="lbGroup", cascade={"remove"})
     * @Groups({"lb_group", "lb_group_single"})
     */
    protected $members;

    /**
     * @var
     * @ORM\Column(name="event_date", type="datetime")
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_calendar", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $eventDate;

    /**
     * @var
     * @Groups({"lb_group_single_mobile"})
     */
    public $joinStatus;

    /**
     * @var
     * @Groups({"lb_group_single_mobile"})
     */
    public $memberRequestStatus;

    /**
     * @var
     * @Groups({"lb_group_single_mobile"})
     */
    public $moderatorRequestStatus;

    /**
     * @var
     * @ORM\Column(name="join_limit", type="integer", nullable=true)
     * @Groups({"lb_group_single", "lb_group_single_mobile"})
     */
    protected $joinLimit;

    /**
     * @var
     * @ORM\Column(name="type", type="boolean")
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $type;

    /**
     * @var
     * @ORM\Column(name="address_name", type="string", nullable=true)
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $addressName;

    /**
     * @var
     * @ORM\Column(name="address", type="string", nullable=true)
     * @Groups({"lb_group", "lb_group_mobile", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $address;

    /**
     * @var
     * @ORM\Column(name="latitude", type="float", nullable=true)
     * @Groups({"lb_group", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $latitude;

    /**
     * @var
     * @ORM\Column(name="longitude", type="float", nullable=true)
     * @Groups({"lb_group", "lb_group_single", "lb_group_single_mobile"})
     */
    protected $longitude;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->eventDate = new \DateTime();
        $this->moderators = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return LBGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return LBGroup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author
     *
     * @param \LB\UserBundle\Entity\User $author
     *
     * @return LBGroup
     */
    public function setAuthor(\LB\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param $user
     * @return bool
     */
    public function isModerator($user)
    {
        // get members
        $members = $this->getModerators();

        // check members
        if($members && $members->count() > 0){

            foreach($members as $member)
            {
                // check user in members
                if($member->getModerator()->getId() == $user->getId()
                    && $member->getAuthorStatus() == 1
                    && $member->getModeratorStatus() == 1)
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $user
     * @return bool
     */
    public function isMember($user)
    {
        // get members
        $members = $this->getMembers();

        // check members
        if($members && $members->count() > 0){

            foreach($members as $member)
            {
                // check user in members
                if($member->getMember()->getId() == $user->getId()
                    && $member->getAuthorStatus() == 1
                    && $member->getMemberStatus() == 1){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $user
     * @return bool
     */
    public function isAuthor($user)
    {
        // check Author
        if(count($this->getAuthor()) > 0){

            // check user in members
            if($this->getAuthor()->getId() == $user->getId()){
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     * @Groups({"lb_group"})
     */
    public function isLimited()
    {
        $limit = $this->getJoinLimit();
        $members = $this->getMembers();

        if($limit == 0 || $limit == null){
            return false;
        }
        elseif($limit != null && ($members->count() <= $limit))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * @param $user
     * @return bool
     */
    public function memberStatuses($user)
    {
        // get members
        $members = $this->getMembers();

        // check members
        if($members && $members->count() > 0){

            foreach($members as $member)
            {
                // check user in members
                if($member->getMember()->getId() == $user->getId()){
                    return array('author_status' => $member->getAuthorStatus(),
                        'member_status' => $member->getMemberStatus());
                }
            }
        }

        return false;
    }

    /**
     * @param $user
     * @return bool
     */
    public function moderatorStatuses($user)
    {
        // get members
        $moderators = $this->getModerators();

        // check members
        if($moderators && $moderators->count() > 0){

            foreach($moderators as $moderator)
            {
                // check user in members
                if($moderator->getModerator()->getId() == $user->getId()){
                    return array('author_status' => $moderator->getAuthorStatus(),
                        'moderator_status' => $moderator->getModeratorStatus());
                }
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * Set eventDate
     *
     * @param \DateTime $eventDate
     *
     * @return LBGroup
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * Get eventDate
     *
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * Set joinLimit
     *
     * @param integer $joinLimit
     *
     * @return LBGroup
     */
    public function setJoinLimit($joinLimit)
    {
        $this->joinLimit = $joinLimit;

        return $this;
    }

    /**
     * Get joinLimit
     *
     * @return integer
     * @Groups({"lb_group"})
     */
    public function getJoinLimit()
    {
        return $this->joinLimit;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return LBGroup
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Add moderator
     *
     * @param \AppBundle\Entity\LBGroupModerators $moderator
     *
     * @return LBGroup
     */
    public function addModerator(\AppBundle\Entity\LBGroupModerators $moderator)
    {
        $this->moderators[] = $moderator;

        return $this;
    }

    /**
     * Remove moderator
     *
     * @param \AppBundle\Entity\LBGroupModerators $moderator
     */
    public function removeModerator(\AppBundle\Entity\LBGroupModerators $moderator)
    {
        $this->moderators->removeElement($moderator);
    }

    /**
     * Get moderators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModerators()
    {
        return $this->moderators;
    }

    /**
     * Add member
     *
     * @param \AppBundle\Entity\LBGroupMembers $member
     *
     * @return LBGroup
     */
    public function addMember(\AppBundle\Entity\LBGroupMembers $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member
     *
     * @param \AppBundle\Entity\LBGroupMembers $member
     */
    public function removeMember(\AppBundle\Entity\LBGroupMembers $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return LBGroup
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     *
     * @return LBGroup
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     *
     * @return LBGroup
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {

        // get $eventDate
        $eventDate = $this->eventDate;
        $now = new \DateTime("now");
        // check birthday
        if (!$eventDate || $eventDate->format('Y-m-d') < $now->format('Y-m-d')) {
            $context->buildViolation('Year must select event date no less then today')
                ->atPath('eventDate')
                ->addViolation();
        }
    }

    /**
     * @return mixed
     */
    public function getAddressName()
    {
        return $this->addressName;
    }

    /**
     * @param mixed $addressName
     */
    public function setAddressName($addressName)
    {
        $this->addressName = $addressName;
    }
}
