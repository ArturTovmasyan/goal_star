<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/23/15
 * Time: 11:09 AM
 */
namespace AppBundle\Entity;

use AppBundle\Traits\File;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use LB\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Interest
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\InterestRepository")
 * @ORM\Table(name="interest")
 * @ORM\HasLifecycleCallbacks()
 */
class Interest
{
    // this trait is add file(UploadFile object) to this entity
    use File;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"interest", "profile_edit", "user_for_mobile"})
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     * @Assert\NotBlank(message = "interest.name.not_blank")
     * @Assert\Length(max=20, maxMessage = "interest.length")
     *
     * @Groups({"interest", "user_for_mobile", "profile_edit"})
     */
    protected $name;

    /**
     * @Assert\NotBlank(message = "interest.position.not_blank")
     * @ORM\Column(name="position", type="integer", nullable=false)
     *
     * @Groups({"interest"})
     */
    protected $position;

    /**
     * @var
     * @Groups({"profile_edit"})
     */
    public $checked = false;

    /**
     * @ORM\ManyToOne(targetEntity="InterestGroup", inversedBy="interest")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     *
     * @Groups({"interest_interestGroup"})
     */
    protected $group;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name ? $this->name : "";
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
     * @return Interest
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
     * @return Interest
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
     * Set group
     *
     * @param \AppBundle\Entity\InterestGroup $group
     *
     * @return Interest
     */
    public function setGroup(\AppBundle\Entity\InterestGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\InterestGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return 'icons';
    }

    /**
     * This function is used to return file web path
     *
     * @VirtualProperty()
     * @Groups({"user_for_mobile", "profile_edit", "interest", "lb_group_mobile", "lb_group_single_mobile", "lb_group", "adGeo"})
     * @return string
     */
    public function getDownloadLinkForMobile()
    {
        $path = "bundles/app/images/no-icon.png";

        if($this->fileName){
            $path = $this->getUploadDir() . '/' . $this->getPath() . '/' . $this->fileName;
        }

        return $this->imageFromCache ? $this->imageFromCache : User::BASE_PATH . $path;
    }

}
