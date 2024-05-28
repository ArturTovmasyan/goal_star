<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/19/16
 * Time: 11:59 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\StateRepository")
 * @ORM\Table(name="state")
 * @UniqueEntity("name", message="Name is already exist")
 * @UniqueEntity("abbr", message="Abbreviation is already exist")
 */
class State
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message = "state.name.not_blank")
     * @Assert\Length(max=50, maxMessage="name.length")
     * @ORM\Column(name="name", type="string", length=50, unique=true)
     *
     */
    protected $name;

    /**
     * @Assert\NotBlank(message = "state.abbr.not_blank")
     * @Assert\Length(max=5, maxMessage="abbr.length")
     * @ORM\Column(name="abbr", type="string", length=5, unique=true)
     *
     */
    protected $abbr;

    /**
     * @return mixed
     */
    function __toString()
    {
        return $this->name;
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
     * @return State
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
     * Set abbr
     *
     * @param string $abbr
     *
     * @return State
     */
    public function setAbbr($abbr)
    {
        $this->abbr = trim(strtolower($abbr));

        return $this;
    }

    /**
     * Get abbr
     *
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }
}
