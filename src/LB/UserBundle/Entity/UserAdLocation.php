<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 12/15/15
 * Time: 2:15 PM
 */

namespace LB\UserBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserAdLocation
 * @package LB\UserBundle\Entity
 * @ORM\Entity(repositoryClass="LB\UserBundle\Entity\Repository\UserAdRepository")
 * @ORM\Table(name="user_ad_location")
 */
class UserAdLocation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    protected $location;


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
     * Set user
     *
     * @param \LB\UserBundle\Entity\User $user
     *
     * @return UserAdLocation
     */
    public function setUser(\LB\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \LB\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set location
     *
     * @param \AppBundle\Entity\Location $location
     *
     * @return UserAdLocation
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
