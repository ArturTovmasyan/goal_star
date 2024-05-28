<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * AdGeo
 * @package AppBundle\Entity
 *
 * @ORM\Table(name="ads_geo")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\AdGeoRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AdGeo
{
    // use file for image
    use \AppBundle\Traits\File;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"adGeo"})
     *
     */
    private $name;

    /**
     * @var float
     * @ORM\Column(name="radius", type="float")
     */
    private $radius;

    /**
     * @var
     *
     * @ORM\Column(name="description", type="text")
     * @Groups({"adGeo"})
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="adGeo", indexBy="id", cascade={"persist"})
     * @var
     */
    protected $locations;

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
     * @return AdGeo
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
     * @return mixed
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * @param mixed $radius
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;
    }

    /**
     * Override get path method from trait
     *
     * @return string
     */
    protected function getPath()
    {
        return 'ad_geo_image';
    }

    function __toString()
    {
        return $this->id ? $this->name . ' ' . $this->radius . ' mile' : '';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add location
     *
     * @param \AppBundle\Entity\Location $location
     *
     * @return Ad
     */
    public function addLocation(\AppBundle\Entity\Location $location)
    {
        $this->locations[] = $location;
        $location->setAdGeo($this);

        return $this;
    }

    /**
     * Remove location
     *
     * @param \AppBundle\Entity\Location $location
     */
    public function removeLocation(\AppBundle\Entity\Location $location)
    {
        $this->locations->removeElement($location);
    }

    /**
     * Get locations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }



    /**
     * This function is used to get locations data by json
     *
     * @return string
     */
    public function getLocationsJson()
    {
        // get default value for location
        $result = array();

        // get locations
        $locations = $this->getLocations();

        // check locations
        if($locations){

            // loop for locations
            foreach($locations as $location){

                // generate array
                $result[] = array(
                    'id' => $location->getId(),
                    'latitude' => $location->getLat(),
                    'longitude' => $location->getLng(),
                ) ;
            }
        }
        return json_encode($result);
    }
}
