<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/23/15
 * Time: 11:10 AM
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InterestGroup
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\InterestGroupRepository")
 * @ORM\Table(name="interest_group")
 */
class InterestGroup
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"interestGroup"})
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message = "interestGroup.name.not_blank")
     * @Assert\Length(max=50, maxMessage = "interestGroup.length")
     *
     * @Groups({"interestGroup"})
     */
    protected $name;

    /**
     * @Assert\NotBlank(message = "interestGroup.position.not_blank")
     * @ORM\Column(name="position", type="integer", nullable=false)
     *
     * @Groups({"interestGroup"})
     */
    protected $position = 0;

    /**
     * @ORM\OneToMany(targetEntity="Interest", mappedBy="group", cascade={"persist", "remove"}, indexBy="id")
     * @ORM\OrderBy({"position"="ASC"})
     *
     * @Groups({"interestGroup_interest"})
     */
    protected $interest;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->interest = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->id ? $this->name : " ";
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
     * @return InterestGroup
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
     * Set position
     *
     * @param integer $position
     *
     * @return InterestGroup
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $interestId
     * @return bool
     */
    public function hasInterest($interestId)
    {
        return isset($this->interest[$interestId]);
    }

    /**
     * Add interest
     *
     * @param \AppBundle\Entity\Interest $interest
     *
     * @return InterestGroup
     */
    public function addInterest(\AppBundle\Entity\Interest $interest)
    {
        $this->interest[$interest->getId()] = $interest;

        return $this;
    }

    /**
     * Remove interest
     *
     * @param \AppBundle\Entity\Interest $interest
     */
    public function removeInterest(\AppBundle\Entity\Interest $interest)
    {
        $this->interest->removeElement($interest);
    }

    /**
     * Get interest
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterest()
    {
        return $this->interest;
    }
}
