<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/7/16
 * Time: 4:45 PM
 */

namespace LB\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * @ORM\Entity()
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="code_unique", columns={"code"})})
 */
class ZipCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="code", nullable=true, unique=true)
     * @var
     */
    protected  $code;

    /**
     * @ORM\Column(type="float", name="lat", nullable=true)
     * @var
     */
    protected  $lat;

    /**
     * @ORM\Column(type="float", name="lng", nullable=true)
     * @var
     */
    protected  $lng;

    /**
     * @ORM\OneToMany(targetEntity="LB\UserBundle\Entity\User", mappedBy="zip")
     * @ORM\JoinColumn(name="zip_id", referencedColumnName="id")
     */
    protected $user;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
    }

    function __toString()
    {
      return (string)$this->id;
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
     * Set code
     *
     * @param integer $code
     *
     * @return ZipCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set lng
     *
     * @param float $lng
     *
     * @return ZipCode
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set lat
     *
     * @param float $lat
     *
     * @return ZipCode
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Add user
     *
     * @param \LB\UserBundle\Entity\User $user
     *
     * @return ZipCode
     */
    public function addUser(\LB\UserBundle\Entity\User $user)
    {
        $this->user[] = $user;
        $user->setZip($this);

        return $this;
    }

    /**
     * Remove user
     *
     * @param \LB\UserBundle\Entity\User $user
     */
    public function removeUser(\LB\UserBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }
}
