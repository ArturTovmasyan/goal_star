<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 11/9/15
 * Time: 10:32 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Location
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\LocationRepository")
 * @ORM\Table(name="location")
 */
class Location
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="float", name="lat", nullable=true)
     * @Groups({"ad_geo"})
     * @var
     */
    protected $lat;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AdGeo", inversedBy="locations", cascade={"persist"})
     * @ORM\JoinColumn(name="ad_geo_id", referencedColumnName="id")
     */
    protected $adGeo;

    /**
     * @ORM\Column(type="float", name="lng", nullable=true)
     * @Groups({"ad_geo"})
     * @var
     */
    protected $lng;

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
     * Set lat
     *
     * @param float $lat
     *
     * @return Location
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
     * Set lng
     *
     * @param float $lng
     *
     * @return Location
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
     * Set adGeo
     *
     * @param \AppBundle\Entity\AdGeo $adGeo
     *
     * @return Location
     */
    public function setAdGeo(\AppBundle\Entity\AdGeo $adGeo = null)
    {
        $this->adGeo = $adGeo;

        return $this;
    }

    /**
     * Get adGeo
     *
     * @return \AppBundle\Entity\AdGeo
     */
    public function getAdGeo()
    {
        return $this->adGeo;
    }
}
