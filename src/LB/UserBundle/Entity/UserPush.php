<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/15/15
 * Time: 1:03 PM
 */

namespace LB\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 *
 * @ORM\Entity(repositoryClass="LB\UserBundle\Entity\Repository\UserPushRepository")
 * @ORM\Table(name="user_push",  uniqueConstraints={@ORM\UniqueConstraint(name="push_unique", columns={"first_user_id", "second_user_id", "location_id" })})
 */
class UserPush
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="first_user_id", referencedColumnName="id")
     */
    protected $firstUser;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="second_user_id", referencedColumnName="id")
     */
    protected $secondUser;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    protected $location;

    /**
     * @ORM\Column(name="date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $date;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return UserPush
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set firstUser
     *
     * @param \LB\UserBundle\Entity\User $firstUser
     *
     * @return UserPush
     */
    public function setFirstUser(\LB\UserBundle\Entity\User $firstUser = null)
    {
        $this->firstUser = $firstUser;

        return $this;
    }

    /**
     * Get firstUser
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getFirstUser()
    {
        return $this->firstUser;
    }

    /**
     * Set secondUser
     *
     * @param \LB\UserBundle\Entity\User $secondUser
     *
     * @return UserPush
     */
    public function setSecondUser(\LB\UserBundle\Entity\User $secondUser = null)
    {
        $this->secondUser = $secondUser;

        return $this;
    }

    /**
     * Get secondUser
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getSecondUser()
    {
        return $this->secondUser;
    }

    /**
     * Set location
     *
     * @param \AppBundle\Entity\Location $location
     *
     * @return UserPush
     */
    public function setLocation(\AppBundle\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return \AppBundle\Entity\Location
     */
    public function getLocation()
    {
        return $this->location;
    }
}
