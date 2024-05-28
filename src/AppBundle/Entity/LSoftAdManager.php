<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 10/31/16
 * Time: 3:55 PM
 */
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class LSoftAdManager
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LSoftAdManagerRepository")
 * @ORM\Table(name="l_soft_ad_manager")
 * @Assert\Callback(methods={"validate"})
 */
class LSoftAdManager
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\Choice(choices = {"4", "5"})
     * @ORM\Column(name="gender", type="smallint", nullable=true)
     */
    protected $gender;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Interest")
     */
    protected $interests;

    /**
     * @ORM\Column(name="city", type="string", length=100, nullable=true)
     * @Assert\Length(
     *     min=3,
     *     max=100
     * )
     */
    protected $city;


    /**
     * @ORM\Column(name="min_age", type="smallint", nullable=true)
     */
    protected $minAge;

    /**
     * @ORM\Column(name="max_age", type="smallint", nullable=true)
     */
    protected $maxAge;

    /**
     * @var
     * @ORM\OneToOne(targetEntity="LSoft\AdBundle\Entity\Ad")
     */
    protected $ad;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->interests = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set gender
     *
     * @param integer $gender
     *
     * @return LSoftAdManager
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return LSoftAdManager
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set minAge
     *
     * @param integer $minAge
     *
     * @return LSoftAdManager
     */
    public function setMinAge($minAge)
    {
        $this->minAge = $minAge;

        return $this;
    }

    /**
     * Get minAge
     *
     * @return integer
     */
    public function getMinAge()
    {
        return $this->minAge;
    }

    /**
     * Set maxAge
     *
     * @param integer $maxAge
     *
     * @return LSoftAdManager
     */
    public function setMaxAge($maxAge)
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * Get maxAge
     *
     * @return integer
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * Add interest
     *
     * @param \AppBundle\Entity\Interest $interest
     *
     * @return LSoftAdManager
     */
    public function addInterest($interest)
    {
        $this->interests[] = $interest;

        return $this;
    }


    /**
     * @param $interest
     * @return mixed
     */
    public function removeInterest($interest)
    {
        $this->interests->removeElement($interest);
        return $this;
    }

    /**
     * Get interests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * Set ad
     *
     * @param \LSoft\AdBundle\Entity\Ad $ad
     *
     * @return LSoftAdManager
     */
    public function setAd(\LSoft\AdBundle\Entity\Ad $ad = null)
    {
        $this->ad = $ad;

        return $this;
    }

    /**
     * Get ad
     *
     * @return \LSoft\AdBundle\Entity\Ad
     */
    public function getAd()
    {
        return $this->ad;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        $minAge = $this->minAge;
        $maxAge = $this->maxAge;
        // check birthday
        if($minAge > 0 && $maxAge > 0 && $maxAge < $minAge ){

                $context->buildViolation('Max age must be larger')
                    ->atPath('maxAge')
                    ->addViolation();
        }
    }
}
