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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LBGroupMembersRepository")
 * @ORM\Table(name="lb_group_members", uniqueConstraints={@ORM\UniqueConstraint(name="lb_group_members_unique_idx", columns={"member_id", "lb_group_id"})})
 *
 * Class GroupProvider
 * @package AppBundle\Entity
 * @UniqueEntity(fields={"member", "lbGroup"}, message="entity.duplicate")
 */
class LBGroupMembers
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(name="author_status", type="boolean")
     * @Groups({"lb_group", "lb_group_single_mobile"})
     */
    protected $authorStatus=false;

    /**
     * @var
     * @ORM\Column(name="member_status", type="boolean")
     * @Groups({"lb_group", "lb_group_single_mobile"})
     */
    protected $memberStatus=false;

    /**
     * @ORM\ManyToOne(targetEntity="LB\UserBundle\Entity\User")
     * @ORM\JoinColumn(fieldName="member_id", referencedColumnName="id")
     * @Groups({"lb_group", "lb_group_single_mobile"})
     */
    protected $member;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LBGroup", inversedBy="members")
     * @ORM\JoinColumn(fieldName="lb_group_id", referencedColumnName="id")
     */
    protected $lbGroup;

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
     * Set authorStatus
     *
     * @param boolean $authorStatus
     *
     * @return LBGroupMembers
     */
    public function setAuthorStatus($authorStatus)
    {
        $this->authorStatus = $authorStatus;

        return $this;
    }

    /**
     * Get authorStatus
     *
     * @return boolean
     */
    public function getAuthorStatus()
    {
        return $this->authorStatus;
    }

    /**
     * Set memberStatus
     *
     * @param boolean $memberStatus
     *
     * @return LBGroupMembers
     */
    public function setMemberStatus($memberStatus)
    {
        $this->memberStatus = $memberStatus;

        return $this;
    }

    /**
     * Get memberStatus
     *
     * @return boolean
     */
    public function getMemberStatus()
    {
        return $this->memberStatus;
    }

    /**
     * Set member
     *
     * @param \LB\UserBundle\Entity\User $member
     *
     * @return LBGroupMembers
     */
    public function setMember(\LB\UserBundle\Entity\User $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set lbGroup
     *
     * @param \AppBundle\Entity\LBGroup $lbGroup
     *
     * @return LBGroupMembers
     */
    public function setLbGroup(\AppBundle\Entity\LBGroup $lbGroup = null)
    {
        $this->lbGroup = $lbGroup;

        return $this;
    }

    /**
     * Get lbGroup
     *
     * @return \AppBundle\Entity\LBGroup
     */
    public function getLbGroup()
    {
        return $this->lbGroup;
    }
}
