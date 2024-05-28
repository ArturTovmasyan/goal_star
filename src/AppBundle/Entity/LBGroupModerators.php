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
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LBGroupModeratorsRepository")
 * @ORM\Table(name="lb_group_moderators", uniqueConstraints={@ORM\UniqueConstraint(name="lb_group_moderators_unique_idx", columns={"moderator_id", "lb_group_id"})})
 *
 * Class GroupProvider
 * @package AppBundle\Entity
 *
 * @UniqueEntity(fields={"moderator", "lbGroup"}, message="entity.duplicate")
 */
class LBGroupModerators
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
     * @ORM\Column(name="moderator", type="boolean")
     * @Groups({"lb_group", "lb_group_single_mobile"})
     */
    protected $moderatorStatus=false;

    /**
     * @ORM\ManyToOne(targetEntity="LB\UserBundle\Entity\User")
     * @ORM\JoinColumn(fieldName="moderator_id", referencedColumnName="id")
     * @Groups({"lb_group", "lb_group_single_mobile"})
     */
    protected $moderator;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\LBGroup", inversedBy="moderators")
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
     * @return LBGroupModerators
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
     * Set moderatorStatus
     *
     * @param boolean $moderatorStatus
     *
     * @return LBGroupModerators
     */
    public function setModeratorStatus($moderatorStatus)
    {
        $this->moderatorStatus = $moderatorStatus;

        return $this;
    }

    /**
     * Get moderatorStatus
     *
     * @return boolean
     */
    public function getModeratorStatus()
    {
        return $this->moderatorStatus;
    }

    /**
     * Set moderator
     *
     * @param \LB\UserBundle\Entity\User $moderator
     *
     * @return LBGroupModerators
     */
    public function setModerator(\LB\UserBundle\Entity\User $moderator = null)
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * Get moderator
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * Set lbGroup
     *
     * @param \AppBundle\Entity\LBGroup $lbGroup
     *
     * @return LBGroupModerators
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
